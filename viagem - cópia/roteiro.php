<?php
require_once 'auth_check.php';
require_once 'classes/GestaoViagens.php';

// Verifica autenticação
$usuario = verificarAutenticacao();

// Verifica se ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$viagem_id = (int)$_GET['id'];

// Instancia o gerenciador de viagens
$gestaoViagens = new GestaoViagens($usuario['usuario_id']);

try {
    // Busca a viagem com seus dias e locais
    $viagem = $gestaoViagens->buscarViagem($viagem_id);
    
    if (!$viagem) {
        throw new Exception("Viagem não encontrada.");
    }
} catch (Exception $e) {
    $_SESSION['erro'] = $e->getMessage();
    header('Location: dashboard.php');
    exit;
}

// Funções auxiliares
function formatarData($data) {
    setlocale(LC_TIME, 'pt_BR.utf8');
    return strftime('%d de %B, %Y', strtotime($data));
}

function formatarHora($hora) {
    return $hora ? date('H:i', strtotime($hora)) : '';
}

function getTipoLocalIcon($tipo) {
    $icons = [
        'atracao' => 'bi-camera',
        'restaurante' => 'bi-cup-hot',
        'hotel' => 'bi-building',
        'transporte' => 'bi-car-front',
        'outro' => 'bi-geo'
    ];
    return $icons[$tipo] ?? 'bi-geo';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= gerarCSRFToken() ?>">
    <title><?= htmlspecialchars($viagem['titulo']) ?> - TravelPlanner</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Sortable.js -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header fixed-header">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="dashboard.php">TravelPlanner</a>
                <div class="d-flex align-items-center">
                    <h6 class="text-white mb-0 me-3 d-none d-md-block">
                        <?= htmlspecialchars($viagem['titulo']) ?>
                    </h6>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#shareModal">
                                <i class="bi bi-share me-2"></i>Compartilhar
                            </a>
                        </li>
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

    <div class="roteiro-container">
        <div class="roteiro-content">
            <div class="container-fluid h-100">
                <div class="row h-100">
                    <!-- Coluna 1: Roteiro Diário -->
                    <div class="col-12 col-lg-3 order-2 order-lg-1">
                        <div class="roteiro-sidebar">
                            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                                <h5 class="mb-0">Roteiro Diário</h5>
                                <div>
                                    <button class="btn btn-sm btn-outline-primary me-2" title="Adicionar dia">
                                        <i class="bi bi-calendar-plus"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary" title="Adicionar local">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Abas dos Dias -->
                            <ul class="nav nav-pills nav-fill sticky-top bg-white border-bottom" id="dayTabs" role="tablist">
                                <?php foreach ($viagem['dias'] as $index => $dia): ?>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link <?= $index === 0 ? 'active' : '' ?>" 
                                                data-bs-toggle="pill" 
                                                data-bs-target="#day<?= $dia['id'] ?>" 
                                                type="button">
                                            Dia <?= $index + 1 ?>
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <!-- Conteúdo dos Dias -->
                            <div class="tab-content" id="dayTabsContent">
                                <?php foreach ($viagem['dias'] as $index => $dia): ?>
                                    <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" 
                                         id="day<?= $dia['id'] ?>" 
                                         role="tabpanel">
                                        <div class="p-2">
                                            <small class="text-muted d-block mb-2">
                                                <?= formatarData($dia['data']) ?>
                                            </small>
                                        </div>
                                        <div class="places-list">
                                            <div class="list-group list-group-flush" data-day-id="<?= $dia['id'] ?>">
                                                <?php if (!empty($dia['locais'])): ?>
                                                    <?php foreach ($dia['locais'] as $local): ?>
                                                        <div class="list-group-item d-flex align-items-center" data-id="<?= $local['id'] ?>">
                                                            <i class="bi bi-grip-vertical drag-handle"></i>
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex justify-content-between align-items-start">
                                                                    <div>
                                                                        <h6 class="mb-1">
                                                                            <i class="bi <?= getTipoLocalIcon($local['tipo']) ?> me-2"></i>
                                                                            <?= htmlspecialchars($local['nome']) ?>
                                                                        </h6>
                                                                        <?php if ($local['hora_inicio']): ?>
                                                                            <span class="time-badge">
                                                                                <i class="bi bi-clock me-1"></i>
                                                                                <?= formatarHora($local['hora_inicio']) ?> - 
                                                                                <?= formatarHora($local['hora_fim']) ?>
                                                                            </span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <i class="bi bi-x-lg remove-place"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <!-- Empty state -->
                                                    <div class="text-center p-4 text-muted">
                                                        <i class="bi bi-plus-circle-dotted display-4"></i>
                                                        <p class="mt-2">Adicione lugares ao seu roteiro</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Coluna 2: Mapa -->
                    <div class="col-12 col-lg-6 order-1 order-lg-2">
                        <div class="map-container">
                            <!-- Controles do Mapa -->
                            <div class="map-controls">
                                <button class="map-control-btn" title="Centralizar mapa">
                                    <i class="bi bi-geo-alt"></i>
                                </button>
                                <button class="map-control-btn" title="Ajustar visualização">
                                    <i class="bi bi-arrows-fullscreen"></i>
                                </button>
                                <button class="map-control-btn" title="Alternar visualização">
                                    <i class="bi bi-layers"></i>
                                </button>
                            </div>

                            <!-- Legenda do Mapa -->
                            <div class="map-legend">
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: var(--accent-color)"></div>
                                    <span class="legend-text">Pontos de parada</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: var(--secondary-color)"></div>
                                    <span class="legend-text">Rota sugerida</span>
                                </div>
                            </div>

                            <!-- Container do Mapa -->
                            <div id="map" class="h-100">
                                <div class="h-100 d-flex align-items-center justify-content-center">
                                    <p class="text-muted">Mapa será carregado aqui</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Coluna 3: Busca e Detalhes -->
                    <div class="col-12 col-lg-3 order-3">
                        <div class="search-container">
                            <!-- Barra de Busca -->
                            <div class="search-box sticky-top">
                                <div class="search-input-group">
                                    <i class="bi bi-search search-icon"></i>
                                    <input type="text" class="form-control" placeholder="Buscar lugares...">
                                </div>
                            </div>

                            <!-- Resultados da Busca -->
                            <div class="search-results">
                                <!-- Empty State (mostrado inicialmente) -->
                                <div class="empty-results">
                                    <i class="bi bi-search"></i>
                                    <p>Digite um local para buscar</p>
                                    <small class="text-muted">Ex: restaurantes, hotéis, pontos turísticos...</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="js/roteiro.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar o mapa
        const map = new RoteiroMap('map', {
            center: [ 
                <?= $viagem['latitude'] ?? 0 ?>, 
                <?= $viagem['longitude'] ?? 0 ?> 
            ],
            zoom: 13
        });

        // Inicializar o gerenciador de roteiro
        const roteiroManager = new RoteiroManager({
            map: map
        });

        // Configurar o primeiro dia como ativo
        const firstDayId = document.querySelector('.list-group').dataset.dayId;
        if (firstDayId) {
            roteiroManager.setActiveDay(firstDayId);
        }
    });
    </script>
</body>
</html>
