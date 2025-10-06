<?php
require_once '../auth_check.php';
require_once '../classes/GestaoViagens.php';

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

// Verifica se os dados necessários foram fornecidos
if (!isset($data['dayId']) || !isset($data['place'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

// Obtém o usuário autenticado
$usuario = verificarAutenticacao();

// Instancia o gerenciador de viagens
$gestaoViagens = new GestaoViagens($usuario['usuario_id']);

try {
    // Prepara os dados do local
    $local = [
        'nome' => $data['place']['name'],
        'tipo' => $data['place']['type'],
        'latitude' => $data['place']['latitude'],
        'longitude' => $data['place']['longitude'],
        'endereco' => $data['place']['address'],
        'hora_inicio' => $data['place']['horaInicio'] ?? null,
        'hora_fim' => $data['place']['horaFim'] ?? null,
        'notas' => $data['place']['notas'] ?? null,
        'preco' => $data['place']['price'] ?? null
    ];

    // Adiciona o local ao roteiro
    $local_id = $gestaoViagens->adicionarLocal($data['dayId'], $local);

    // Retorna os dados do local adicionado
    echo json_encode([
        'success' => true,
        'message' => 'Local adicionado com sucesso',
        'place' => [
            'id' => $local_id,
            'nome' => $local['nome'],
            'tipo' => $local['tipo'],
            'latitude' => $local['latitude'],
            'longitude' => $local['longitude'],
            'endereco' => $local['endereco'],
            'hora_inicio' => $local['hora_inicio'],
            'hora_fim' => $local['hora_fim'],
            'notas' => $local['notas'],
            'ordem' => $data['order'] ?? null
        ]
    ]);

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
