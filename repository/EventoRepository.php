<?php
require_once __DIR__ . '/../config/database.php';

class EventoRepository {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function criar($dados) {
        $sql = "INSERT INTO Evento (nome, descricao, data_inicio, data_fim, id_categoria, id_local, foto) 
                VALUES (:nome, :descricao, :data_inicio, :data_fim, :id_categoria, :id_local, :foto)";
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindParam(':nome', $dados['nome']);
        $stmt->bindParam(':descricao', $dados['descricao']);
        $stmt->bindParam(':data_inicio', $dados['data_inicio']);
        $stmt->bindParam(':data_fim', $dados['data_fim']);
        $stmt->bindParam(':id_categoria', $dados['id_categoria']);
        $stmt->bindParam(':id_local', $dados['id_local']);
        
        // Tratar foto como BLOB
        if (isset($dados['foto']) && !empty($dados['foto'])) {
            $stmt->bindParam(':foto', $dados['foto'], PDO::PARAM_LOB);
        } else {
            $fotoNull = null;
            $stmt->bindParam(':foto', $fotoNull, PDO::PARAM_NULL);
        }
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function listar() {
        $sql = "SELECT e.*, c.nome as categoria_nome, l.nome as local_nome, l.campus, l.sala 
                FROM Evento e 
                LEFT JOIN Categoria c ON e.id_categoria = c.id_categoria 
                LEFT JOIN Local l ON e.id_local = l.id_local
                ORDER BY e.data_inicio DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function buscarPorId($id) {
        $sql = "SELECT e.*, c.nome as categoria_nome, l.nome as local_nome, l.campus, l.sala 
                FROM Evento e 
                LEFT JOIN Categoria c ON e.id_categoria = c.id_categoria 
                LEFT JOIN Local l ON e.id_local = l.id_local
                WHERE e.id_evento = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function atualizar($id, $dados) {
        // Verificar se a foto está sendo atualizada
        if (isset($dados['foto'])) {
            $sql = "UPDATE Evento SET 
                    nome = :nome, 
                    descricao = :descricao, 
                    data_inicio = :data_inicio, 
                    data_fim = :data_fim, 
                    id_categoria = :id_categoria, 
                    id_local = :id_local,
                    foto = :foto
                    WHERE id_evento = :id";
            
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':descricao', $dados['descricao']);
            $stmt->bindParam(':data_inicio', $dados['data_inicio']);
            $stmt->bindParam(':data_fim', $dados['data_fim']);
            $stmt->bindParam(':id_categoria', $dados['id_categoria']);
            $stmt->bindParam(':id_local', $dados['id_local']);
            
            // Tratar foto como BLOB
            if (!empty($dados['foto'])) {
                $stmt->bindParam(':foto', $dados['foto'], PDO::PARAM_LOB);
            } else {
                $fotoNull = null;
                $stmt->bindParam(':foto', $fotoNull, PDO::PARAM_NULL);
            }
        } else {
            // Atualizar sem modificar a foto
            $sql = "UPDATE Evento SET 
                    nome = :nome, 
                    descricao = :descricao, 
                    data_inicio = :data_inicio, 
                    data_fim = :data_fim, 
                    id_categoria = :id_categoria, 
                    id_local = :id_local 
                    WHERE id_evento = :id";
            
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':descricao', $dados['descricao']);
            $stmt->bindParam(':data_inicio', $dados['data_inicio']);
            $stmt->bindParam(':data_fim', $dados['data_fim']);
            $stmt->bindParam(':id_categoria', $dados['id_categoria']);
            $stmt->bindParam(':id_local', $dados['id_local']);
        }
        
        return $stmt->execute();
    }
    
    public function deletar($id) {
        $sql = "DELETE FROM Evento WHERE id_evento = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    public function verificarInscricoesAtivas($id_evento) {
        $sql = "SELECT COUNT(*) FROM Inscricao 
                WHERE id_evento = :id_evento 
                AND status IN ('Confirmada', 'Presente')";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_evento', $id_evento);
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }
    
    public function deletarComCascata($id_evento) {
        try {
            // Iniciar transação
            $this->conn->beginTransaction();
            
            // 1. Excluir relacionamentos Atividade_Palestrante (via atividades do evento)
            $sql = "DELETE FROM Atividade_Palestrante 
                    WHERE id_atividade IN (
                        SELECT id_atividade FROM Atividade WHERE id_evento = :id_evento
                    )";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_evento', $id_evento);
            $stmt->execute();
            
            // 2. Excluir atividades do evento
            $sql = "DELETE FROM Atividade WHERE id_evento = :id_evento";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_evento', $id_evento);
            $stmt->execute();
            
            // 3. Excluir certificados do evento
            $sql = "DELETE FROM Certificado WHERE id_evento = :id_evento";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_evento', $id_evento);
            $stmt->execute();
            
            // 4. Excluir feedbacks do evento
            $sql = "DELETE FROM Feedback WHERE id_evento = :id_evento";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_evento', $id_evento);
            $stmt->execute();
            
            // 5. Excluir premiações do evento
            $sql = "DELETE FROM Premiacao WHERE id_evento = :id_evento";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_evento', $id_evento);
            $stmt->execute();
            
            // 6. Excluir relacionamentos organizador-evento
            $sql = "DELETE FROM Organiza_Evento WHERE id_evento = :id_evento";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_evento', $id_evento);
            $stmt->execute();
            
            // 7. Excluir inscrições do evento
            $sql = "DELETE FROM Inscricao WHERE id_evento = :id_evento";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_evento', $id_evento);
            $stmt->execute();
            
            // 8. Finalmente, excluir o evento
            $sql = "DELETE FROM Evento WHERE id_evento = :id_evento";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_evento', $id_evento);
            $result = $stmt->execute();
            
            // Confirmar transação
            $this->conn->commit();
            
            return $result;
            
        } catch (Exception $e) {
            // Reverter transação em caso de erro
            $this->conn->rollBack();
            throw new Exception("Erro ao excluir evento: " . $e->getMessage());
        }
    }
    
    public function listarCategorias() {
        $sql = "SELECT * FROM Categoria ORDER BY nome";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function listarLocais() {
        $sql = "SELECT * FROM Local ORDER BY nome";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>