<?php
require_once 'auth_check.php';
require_once 'classes/GestaoViagens.php';

// Verifica se a requisição é POST e JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || 
    !isset($_SERVER['CONTENT_TYPE']) || 
    strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Requisição inválida']);
    exit;
}

// Obtém o JSON do corpo da requisição
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Verifica se o JSON é válido
if ($data === null) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'JSON inválido']);
    exit;
}

// Verifica o token CSRF
if (!isset($data['csrf_token']) || !verificarCSRFToken($data['csrf_token'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
    exit;
}

// Obtém o usuário autenticado
$usuario = verificarAutenticacao();

// Instancia o gerenciador de viagens
$gestaoViagens = new GestaoViagens($usuario['usuario_id']);

try {
    // Processa diferentes ações baseadas no campo 'action'
    switch ($data['action']) {
        case 'updateOrder':
            // Valida os dados necessários
            if (!isset($data['dayId']) || !is_numeric($data['dayId']) ||
                !isset($data['order']) || !is_array($data['order'])) {
                throw new Exception('Dados inválidos para atualização de ordem');
            }

            // Atualiza a ordem dos locais
            $gestaoViagens->atualizarOrdemLocais($data['dayId'], $data['order']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Ordem atualizada com sucesso'
            ]);
            break;

        case 'removeLocal':
            // Valida os dados necessários
            if (!isset($data['localId']) || !is_numeric($data['localId'])) {
                throw new Exception('ID do local não fornecido');
            }

            // Remove o local do roteiro
            $gestaoViagens->removerLocal($data['localId']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Local removido com sucesso'
            ]);
            break;

        case 'addLocal':
            // Valida os dados necessários
            if (!isset($data['diaId']) || !is_numeric($data['diaId']) ||
                !isset($data['nome']) || empty($data['nome']) ||
                !isset($data['tipo']) || empty($data['tipo'])) {
                throw new Exception('Dados inválidos para adição de local');
            }

            // Prepara os dados do local
            $local = [
                'nome' => $data['nome'],
                'tipo' => $data['tipo'],
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'endereco' => $data['endereco'] ?? null,
                'hora_inicio' => $data['horaInicio'] ?? null,
                'hora_fim' => $data['horaFim'] ?? null,
                'notas' => $data['notas'] ?? null
            ];

            // Adiciona o local ao roteiro
            $novoLocalId = $gestaoViagens->adicionarLocal($data['diaId'], $local);
            
            echo json_encode([
                'success' => true,
                'message' => 'Local adicionado com sucesso',
                'localId' => $novoLocalId
            ]);
            break;

        case 'addDay':
            // Valida os dados necessários
            if (!isset($data['viagemId']) || !is_numeric($data['viagemId']) ||
                !isset($data['data']) || !strtotime($data['data'])) {
                throw new Exception('Dados inválidos para adição de dia');
            }

            // Adiciona um novo dia ao roteiro
            $novoDiaId = $gestaoViagens->adicionarDiaRoteiro($data['viagemId'], $data['data']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Dia adicionado com sucesso',
                'diaId' => $novoDiaId
            ]);
            break;

        case 'removeDay':
            // Valida os dados necessários
            if (!isset($data['diaId']) || !is_numeric($data['diaId'])) {
                throw new Exception('ID do dia não fornecido');
            }

            // Remove o dia do roteiro
            $gestaoViagens->removerDiaRoteiro($data['diaId']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Dia removido com sucesso'
            ]);
            break;

        case 'updateLocal':
            // Valida os dados necessários
            if (!isset($data['localId']) || !is_numeric($data['localId'])) {
                throw new Exception('ID do local não fornecido');
            }

            // Prepara os dados do local
            $local = [
                'nome' => $data['nome'] ?? null,
                'tipo' => $data['tipo'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'endereco' => $data['endereco'] ?? null,
                'hora_inicio' => $data['horaInicio'] ?? null,
                'hora_fim' => $data['horaFim'] ?? null,
                'notas' => $data['notas'] ?? null
            ];

            // Atualiza os dados do local
            $gestaoViagens->atualizarLocal($data['localId'], $local);
            
            echo json_encode([
                'success' => true,
                'message' => 'Local atualizado com sucesso'
            ]);
            break;

        default:
            throw new Exception('Ação inválida');
    }
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
