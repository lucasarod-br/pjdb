<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../services/AtividadeService.php';

try {
    $atividadeService = new AtividadeService();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $atividade = $atividadeService->buscarAtividadePorId($_GET['id']);
                echo json_encode(['success' => true, 'data' => $atividade]);
            } elseif (isset($_GET['id_evento'])) {
                $atividades = $atividadeService->listarAtividadesPorEvento($_GET['id_evento']);
                echo json_encode($atividades);
            } else {
                $atividades = $atividadeService->listarAtividades();
                echo json_encode($atividades);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $atividadeService->criarAtividade($input);
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'Atividade criada com sucesso']);
            break;
            
        case 'PUT':
            if (!isset($_GET['id'])) {
                throw new Exception('ID é obrigatório para atualização');
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $atividadeService->atualizarAtividade($_GET['id'], $input);
            echo json_encode(['success' => true, 'message' => 'Atividade atualizada com sucesso']);
            break;
            
        case 'DELETE':
            if (!isset($_GET['id'])) {
                throw new Exception('ID é obrigatório para exclusão');
            }
            $atividadeService->deletarAtividade($_GET['id']);
            echo json_encode(['success' => true, 'message' => 'Atividade excluída com sucesso']);
            break;
            
        default:
            throw new Exception('Método não permitido');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
