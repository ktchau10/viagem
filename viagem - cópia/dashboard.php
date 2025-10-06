<?php
require_once 'auth_check.php';
require_once 'classes/GestaoViagens.php';

// Verifica autenticação
$usuario = verificarAutenticacao();

// Instancia o gerenciador de viagens
$gestaoViagens = new GestaoViagens($usuario['usuario_id']);

// Busca as viagens do usuário
try {
    $viagens = $gestaoViagens->listarViagens();
} catch (Exception $e) {
    $erro = $e->getMessage();
}

// Função auxiliar para formatar data
function formatarData($data) {
    return date('d/m/Y', strtotime($data));
}

// Função para calcular duração da viagem
function calcularDuracao($inicio, $fim) {
    $data_inicio = new DateTime($inicio);
    $data_fim = new DateTime($fim);
    $intervalo = $data_inicio->diff($data_fim);
    return $intervalo->days + 1; // Incluindo o dia inicial
}

// Função para obter classe de status
function getStatusClass($status) {
    $classes = [
        'planejamento' => 'bg-info',
        'ativa' => 'bg-success',
        'concluida' => 'bg-secondary',
        'cancelada' => 'bg-danger'
    ];
    return $classes[$status] ?? 'bg-secondary';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TravelPlanner</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    <style>
        /* Estilo para o campo de data */
        input[type="date"] {
            position: relative;
            padding-right: 30px;
        }
        
        input[type="date"]::-webkit-calendar-picker-indicator {
            position: absolute;
            right: 8px;
            cursor: pointer;
        }

        /* Mostra a data formatada */
        input[type="date"]:not(:focus):not(:placeholder-shown)::before {
            content: attr(data-date);
            position: absolute;
            left: 12px;
            color: #212529;
        }

        input[type="date"]:not(:focus):not(:placeholder-shown) {
            color: transparent;
        }
    </style>
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
            <!-- Título e Botão Nova Viagem -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">Minhas Viagens</h1>
                <a href="nova_viagem.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Nova Viagem
                </a>
            </div>

            <!-- Mensagem de Sucesso -->
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Viagem criada com sucesso!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Mensagem de Erro -->
            <?php if (isset($erro)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($erro) ?>
            </div>
            <?php endif; ?>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form id="formFiltros" class="row g-3">
                        <div class="col-md-4">
                            <label for="filtroStatus" class="form-label">Status</label>
                            <select class="form-select" id="filtroStatus">
                                <option value="">Todos</option>
                                <option value="planejamento">Planejamento</option>
                                <option value="ativa">Ativa</option>
                                <option value="concluida">Concluída</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filtroDataInicio" class="form-label">Data Início</label>
                            <input type="date" class="form-control" id="filtroDataInicio" name="data_inicio"
                                   pattern="\d{2}/\d{2}/\d{4}"
                                   min="2020-01-01"
                                   max="2030-12-31">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-filter me-2"></i>Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Viagens -->
            <div class="row g-4">
                <?php if (!empty($viagens)): ?>
                    <?php foreach ($viagens as $viagem): ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card trip-card h-100">
                                <div class="card-body">
                                    <span class="badge <?= getStatusClass($viagem['status']) ?> position-absolute top-0 end-0 mt-3 me-3">
                                        <?= ucfirst($viagem['status']) ?>
                                    </span>
                                    
                                    <h5 class="card-title"><?= htmlspecialchars($viagem['titulo']) ?></h5>
                                    
                                    <p class="trip-dates">
                                        <i class="bi bi-calendar3 me-2"></i>
                                        <?= formatarData($viagem['data_inicio']) ?> - <?= formatarData($viagem['data_fim']) ?>
                                        <small class="d-block text-muted mt-1">
                                            <?= calcularDuracao($viagem['data_inicio'], $viagem['data_fim']) ?> dias
                                        </small>
                                    </p>
                                    
                                    <?php if (!empty($viagem['descricao'])): ?>
                                        <p class="card-text"><?= htmlspecialchars($viagem['descricao']) ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div>
                                            <small class="text-muted me-3">
                                                <i class="bi bi-geo-alt me-1"></i><?= $viagem['total_locais'] ?> lugares
                                            </small>
                                        </div>
                                        <div class="btn-group">
                                            <a href="roteiro.php?id=<?= $viagem['id'] ?>" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-map me-1"></i>Ver Roteiro
                                            </a>
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    onclick="confirmarExclusao(<?= $viagem['id'] ?>, '<?= htmlspecialchars($viagem['titulo'], ENT_QUOTES) ?>')"
                                                    <?= ($viagem['status'] === 'concluida') ? 'disabled' : '' ?>>
                                                <i class="bi bi-trash me-1"></i>Excluir
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-compass display-1 text-muted"></i>
                            <h3 class="mt-3">Nenhuma viagem encontrada</h3>
                            <p class="text-muted">Comece criando sua primeira viagem!</p>
                            <a href="nova_viagem.php" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-2"></i>Criar Nova Viagem
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="modalConfirmacao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir a viagem <strong id="viagemTitulo"></strong>?</p>
                    <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="formExclusao" method="POST" action="process_viagem.php" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= gerarCSRFToken() ?>">
                        <input type="hidden" name="_method" value="DELETE">
                        <input type="hidden" name="id" id="viagemId">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Excluir Viagem
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const formFiltros = document.getElementById('formFiltros');
        const filtroDataInicio = document.getElementById('filtroDataInicio');

        // Configurar data máxima como hoje
        const hoje = new Date().toISOString().split('T')[0];
        filtroDataInicio.max = hoje;
        
        // Formatar data para exibição dd/mm/aaaa
        filtroDataInicio.addEventListener('change', function(e) {
            const data = new Date(this.value);
            if (!isNaN(data.getTime())) {
                const dia = String(data.getDate()).padStart(2, '0');
                const mes = String(data.getMonth() + 1).padStart(2, '0');
                const ano = data.getFullYear();
                this.setAttribute('data-date', `${dia}/${mes}/${ano}`);
            }
        });
        const modal = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
        
        // Função para confirmar exclusão
        window.confirmarExclusao = function(id, titulo) {
            document.getElementById('viagemId').value = id;
            document.getElementById('viagemTitulo').textContent = titulo;
            modal.show();
        };

        formFiltros.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const status = document.getElementById('filtroStatus').value;
            const dataInicio = document.getElementById('filtroDataInicio').value;
            
            let url = 'process_viagem.php?';
            if (status) url += `status=${status}&`;
            if (dataInicio) url += `data_inicio=${dataInicio}&`;
            
            try {
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.success) {
                    location.reload(); // Recarrega a página com os filtros aplicados
                } else {
                    alert(data.message || 'Erro ao aplicar filtros.');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao processar a requisição.');
            }
        });

        // Auto-ocultar alertas após 5 segundos
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.remove('show');
            }, 5000);
        });
    });
    </script>
</body>
</html>
