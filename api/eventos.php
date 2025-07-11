<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../services/EventoService.php';

try {
    $eventoService = new EventoService();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $evento = $eventoService->buscarEventoPorId($_GET['id']);
                echo json_encode(['success' => true, 'data' => $evento]);
            } else {
                $eventos = $eventoService->listarEventos();
                echo json_encode($eventos);
            }
            break;
            
        case 'POST':
            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
            
            if (strpos($contentType, 'multipart/form-data') !== false) {
                $input = [
                    'nome' => $_POST['nome'] ?? '',
                    'descricao' => $_POST['descricao'] ?? '',
                    'data_inicio' => $_POST['data_inicio'] ?? '',
                    'data_fim' => $_POST['data_fim'] ?? '',
                    'id_categoria' => $_POST['id_categoria'] ?? '',
                    'id_local' => $_POST['id_local'] ?? ''
                ];
                
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $input['foto'] = $eventoService->processarFotoUpload($_FILES['foto']);
                }
            } else {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (isset($input['foto']) && !empty($input['foto'])) {
                    $input['foto'] = $eventoService->processarFotoBase64($input['foto']);
                }
            }
            
            $id = $eventoService->criarEvento($input);
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'Evento criado com sucesso']);
            break;
            
        case 'PUT':
            if (!isset($_GET['id'])) {
                throw new Exception('ID é obrigatório para atualização');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (isset($input['foto']) && !empty($input['foto'])) {
                $input['foto'] = $eventoService->processarFotoBase64($input['foto']);
            }
            
            $eventoService->atualizarEvento($_GET['id'], $input);
            echo json_encode(['success' => true, 'message' => 'Evento atualizado com sucesso']);
            break;
            
        case 'DELETE':
            if (!isset($_GET['id'])) {
                throw new Exception('ID é obrigatório para exclusão');
            }
            $eventoService->deletarEvento($_GET['id']);
            echo json_encode(['success' => true, 'message' => 'Evento excluído com sucesso']);
            break;
            
        default:
            throw new Exception('Método não permitido');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>