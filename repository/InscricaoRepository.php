<?php
require_once __DIR__ . '/../config/database.php';

class InscricaoRepository {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function criar($dados) {
        $sql = "INSERT INTO Inscricao (id_participante, id_evento, status, data_inscricao) 
                VALUES (:id_participante, :id_evento, :status, :data_inscricao)";
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindParam(':id_participante', $dados['id_participante']);
        $stmt->bindParam(':id_evento', $dados['id_evento']);
        $stmt->bindParam(':status', $dados['status']);
        $stmt->bindParam(':data_inscricao', $dados['data_inscricao']);
        
        return $stmt->execute();
    }
    
    public function listar() {
        $sql = "SELECT i.*, p.nome as participante_nome, p.email, p.matricula, p.curso,
                       e.nome as evento_nome, e.data_inicio, e.data_fim
                FROM Inscricao i 
                LEFT JOIN Participante p ON i.id_participante = p.id_participante 
                LEFT JOIN Evento e ON i.id_evento = e.id_evento 
                ORDER BY i.data_inscricao DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function listarPorEvento($id_evento) {
        $sql = "SELECT i.*, p.nome as participante_nome, p.email, p.matricula, p.curso
                FROM Inscricao i 
                LEFT JOIN Participante p ON i.id_participante = p.id_participante 
                WHERE i.id_evento = :id_evento 
                ORDER BY i.data_inscricao DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_evento', $id_evento);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function listarPorParticipante($id_participante) {
        $sql = "SELECT i.*, e.nome as evento_nome, e.data_inicio, e.data_fim
                FROM Inscricao i 
                LEFT JOIN Evento e ON i.id_evento = e.id_evento 
                WHERE i.id_participante = :id_participante 
                ORDER BY i.data_inscricao DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_participante', $id_participante);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function buscarPorId($id_participante, $id_evento) {
        $sql = "SELECT i.*, p.nome as participante_nome, p.email, p.matricula, p.curso,
                       e.nome as evento_nome, e.data_inicio, e.data_fim
                FROM Inscricao i 
                LEFT JOIN Participante p ON i.id_participante = p.id_participante 
                LEFT JOIN Evento e ON i.id_evento = e.id_evento 
                WHERE i.id_participante = :id_participante AND i.id_evento = :id_evento";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_participante', $id_participante);
        $stmt->bindParam(':id_evento', $id_evento);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function atualizar($id_participante, $id_evento, $dados) {
        $sql = "UPDATE Inscricao SET 
                status = :status, 
                data_inscricao = :data_inscricao 
                WHERE id_participante = :id_participante AND id_evento = :id_evento";
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindParam(':id_participante', $id_participante);
        $stmt->bindParam(':id_evento', $id_evento);
        $stmt->bindParam(':status', $dados['status']);
        $stmt->bindParam(':data_inscricao', $dados['data_inscricao']);
        
        return $stmt->execute();
    }
    
    public function deletar($id_participante, $id_evento) {
        $sql = "DELETE FROM Inscricao WHERE id_participante = :id_participante AND id_evento = :id_evento";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_participante', $id_participante);
        $stmt->bindParam(':id_evento', $id_evento);
        
        return $stmt->execute();
    }
    
    public function listarParticipantes() {
        $sql = "SELECT * FROM Participante ORDER BY nome";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function listarEventos() {
        $sql = "SELECT id_evento, nome, data_inicio, data_fim FROM Evento ORDER BY data_inicio DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function verificarInscricaoExistente($id_participante, $id_evento) {
        $sql = "SELECT COUNT(*) FROM Inscricao WHERE id_participante = :id_participante AND id_evento = :id_evento";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_participante', $id_participante);
        $stmt->bindParam(':id_evento', $id_evento);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }
}
?>
