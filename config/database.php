<?php
class Database {
    private $host = '127.0.0.1';
    private $db_name = 'pj-db';
    private $username = 'root';
    private $password = 'minhasenha';
    private $port = 3306;
    private $conn;

    public function getConnection() {
        if ($this->conn != null) {
            return $this->conn;
        }

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $e) {
            error_log("Erro na conexão: " . $e->getMessage());
            throw new Exception("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
        
        return $this->conn;
    }
}
?>