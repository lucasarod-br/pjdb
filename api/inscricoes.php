<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../services/InscricaoService.php';

try {
    $inscricaoService = new InscricaoService();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id_participante']) && isset($_GET['id_evento'])) {
                $inscricao = $inscricaoService->buscarInscricaoPorId($_GET['id_participante'], $_GET['id_evento']);
                echo json_encode(['success' => true, 'data' => $inscricao]);
            } elseif (isset($_GET['id_evento'])) {
                $inscricoes = $inscricaoService->listarInscricoesPorEvento($_GET['id_evento']);
                echo json_encode($inscricoes);
            } elseif (isset($_GET['id_participante'])) {
                $inscricoes = $inscricaoService->listarInscricoesPorParticipante($_GET['id_participante']);
                echo json_encode($inscricoes);
            } else {
                $inscricoes = $inscricaoService->listarInscricoes();
                echo json_encode($inscricoes);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $inscricaoService->criarInscricao($input);
            echo json_encode(['success' => true, 'message' => 'Inscrição criada com sucesso']);
            break;
            
        case 'PUT':
            if (!isset($_GET['id_participante']) || !isset($_GET['id_evento'])) {
                throw new Exception('ID do participante e do evento são obrigatórios para atualização');
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $inscricaoService->atualizarInscricao($_GET['id_participante'], $_GET['id_evento'], $input);
            echo json_encode(['success' => true, 'message' => 'Inscrição atualizada com sucesso']);
            break;
            
        case 'DELETE':
            if (!isset($_GET['id_participante']) || !isset($_GET['id_evento'])) {
                throw new Exception('ID do participante e do evento são obrigatórios para exclusão');
            }
            $inscricaoService->deletarInscricao($_GET['id_participante'], $_GET['id_evento']);
            echo json_encode(['success' => true, 'message' => 'Inscrição excluída com sucesso']);
            break;
            
        default:
            throw new Exception('Método não permitido');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
