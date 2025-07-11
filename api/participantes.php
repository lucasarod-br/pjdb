<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../services/InscricaoService.php';

try {
    $inscricaoService = new InscricaoService();
    $participantes = $inscricaoService->obterParticipantes();
    echo json_encode($participantes);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
