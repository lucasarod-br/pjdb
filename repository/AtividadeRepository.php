<?php
require_once __DIR__ . '/../config/database.php';

class AtividadeRepository {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function criar($dados) {
        $sql = "INSERT INTO Atividade (titulo, tipo, id_evento) VALUES (:titulo, :tipo, :id_evento)";
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindParam(':titulo', $dados['titulo']);
        $stmt->bindParam(':tipo', $dados['tipo']);
        $stmt->bindParam(':id_evento', $dados['id_evento']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function listar() {
        $sql = "SELECT a.*, e.nome as evento_nome, e.data_inicio, e.data_fim 
                FROM Atividade a 
                LEFT JOIN Evento e ON a.id_evento = e.id_evento 
                ORDER BY e.data_inicio DESC, a.titulo";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function listarPorEvento($id_evento) {
        $sql = "SELECT a.*, e.nome as evento_nome 
                FROM Atividade a 
                LEFT JOIN Evento e ON a.id_evento = e.id_evento 
                WHERE a.id_evento = :id_evento 
                ORDER BY a.titulo";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_evento', $id_evento);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function buscarPorId($id) {
        $sql = "SELECT a.*, e.nome as evento_nome, e.data_inicio, e.data_fim 
                FROM Atividade a 
                LEFT JOIN Evento e ON a.id_evento = e.id_evento 
                WHERE a.id_atividade = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function atualizar($id, $dados) {
        $sql = "UPDATE Atividade SET 
                titulo = :titulo, 
                tipo = :tipo, 
                id_evento = :id_evento 
                WHERE id_atividade = :id";
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':titulo', $dados['titulo']);
        $stmt->bindParam(':tipo', $dados['tipo']);
        $stmt->bindParam(':id_evento', $dados['id_evento']);
        
        return $stmt->execute();
    }
    
    public function deletar($id) {
        $sql = "DELETE FROM Atividade WHERE id_atividade = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    public function listarEventos() {
        $sql = "SELECT id_evento, nome, data_inicio, data_fim FROM Evento ORDER BY data_inicio DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
