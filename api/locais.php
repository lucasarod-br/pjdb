<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../services/EventoService.php';

try {
    $eventoService = new EventoService();
    $locais = $eventoService->obterLocais();
    echo json_encode($locais);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
