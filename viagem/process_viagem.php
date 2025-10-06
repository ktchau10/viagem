<?php
require_once 'auth_check.php';
require_once 'classes/GestaoViagens.php';

// Verifica autenticação
$usuario = verificarAutenticacao();

// Verifica token CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    if (!isset($_POST['csrf_token']) || !verificarCSRFToken($_POST['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Token CSRF inválido']);
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
                'titulo' => filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING),
                'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING),
                'data_inicio' => filter_input(INPUT_POST, 'data_inicio'),
                'data_fim' => filter_input(INPUT_POST, 'data_fim')
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
            echo json_encode([
                'success' => true,
                'message' => 'Viagem criada com sucesso!',
                'viagem_id' => $viagem_id
            ]);
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
            parse_str(file_get_contents("php://input"), $delete_vars);
            
            if (!isset($delete_vars['id'])) {
                throw new Exception("ID da viagem não fornecido.");
            }

            $gestaoViagens->excluirViagem($delete_vars['id']);
            echo json_encode([
                'success' => true,
                'message' => 'Viagem excluída com sucesso!'
            ]);
            break;

        default:
            throw new Exception("Método não suportado");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
