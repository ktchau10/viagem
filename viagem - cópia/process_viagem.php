<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'auth_check.php';
require_once 'classes/GestaoViagens.php';

// Verifica autenticação
$usuario = verificarAutenticacao();

// Verifica token CSRF para requisições POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verificarCSRFToken($_POST['csrf_token'])) {
        header('Location: nova_viagem.php?error=csrf');
        exit;
    }
}

// Instancia o gerenciador de viagens
$gestaoViagens = new GestaoViagens($usuario['usuario_id']);

// Define a resposta como JSON
header('Content-Type: application/json');

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            // Criar nova viagem
            $dados = [
                'titulo' => htmlspecialchars(trim($_POST['titulo'] ?? '')),
                'descricao' => htmlspecialchars(trim($_POST['descricao'] ?? '')),
                'data_inicio' => trim($_POST['data_inicio'] ?? ''),
                'data_fim' => trim($_POST['data_fim'] ?? '')
            ];

            // Validações
            if (empty($dados['titulo']) || empty($dados['data_inicio']) || empty($dados['data_fim'])) {
                throw new Exception("Campos obrigatórios não preenchidos.");
            }

            // Validar datas
            $inicio = new DateTime($dados['data_inicio']);
            $fim = new DateTime($dados['data_fim']);
            
            if ($fim < $inicio) {
                throw new Exception("A data final não pode ser anterior à data inicial.");
            }

            $viagem_id = $gestaoViagens->criarViagem($dados);
            
            // Redirecionar diretamente para o dashboard
            header('Location: dashboard.php?success=1');
            exit;
            break;

        case 'GET':
            if (isset($_GET['id'])) {
                // Buscar uma viagem específica
                $viagem = $gestaoViagens->buscarViagem($_GET['id']);
                echo json_encode(['success' => true, 'viagem' => $viagem]);
            } else {
                // Listar todas as viagens com filtros
                $filtros = [
                    'status' => filter_input(INPUT_GET, 'status'),
                    'data_inicio' => filter_input(INPUT_GET, 'data_inicio')
                ];
                $viagens = $gestaoViagens->listarViagens($filtros);
                echo json_encode(['success' => true, 'viagens' => $viagens]);
            }
            break;

        case 'PUT':
            // Atualizar viagem existente
            parse_str(file_get_contents("php://input"), $put_vars);
            
            if (!isset($put_vars['id'])) {
                throw new Exception("ID da viagem não fornecido.");
            }

            $dados = [
                'titulo' => filter_var($put_vars['titulo'], FILTER_SANITIZE_STRING),
                'descricao' => filter_var($put_vars['descricao'], FILTER_SANITIZE_STRING),
                'data_inicio' => $put_vars['data_inicio'],
                'data_fim' => $put_vars['data_fim'],
                'status' => $put_vars['status']
            ];

            $gestaoViagens->atualizarViagem($put_vars['id'], $dados);
            echo json_encode([
                'success' => true,
                'message' => 'Viagem atualizada com sucesso!'
            ]);
            break;

        case 'DELETE':
            // Excluir viagem
            $method = $_POST['_method'] ?? '';
            if ($method === 'DELETE' && isset($_POST['id'])) {
                $gestaoViagens->excluirViagem($_POST['id']);
                header('Location: dashboard.php?success=1&message=' . urlencode('Viagem excluída com sucesso!'));
                exit;
            }
            
            throw new Exception("Requisição de exclusão inválida.");
            break;

        default:
            throw new Exception("Método não suportado");
    }

} catch (Exception $e) {
    error_log('Erro ao processar viagem: ' . $e->getMessage());
    http_response_code(400);
    
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    
    // Se a viagem foi criada apesar do erro, incluir o ID
    if (isset($viagem_id)) {
        $response['viagem_id'] = $viagem_id;
        error_log('Viagem foi criada apesar do erro. ID: ' . $viagem_id);
    }
    
    echo json_encode($response);
    exit;
}
?>
