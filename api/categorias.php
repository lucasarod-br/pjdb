<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../services/EventoService.php';

try {
    $eventoService = new EventoService();
    $categorias = $eventoService->obterCategorias();
    echo json_encode($categorias);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
