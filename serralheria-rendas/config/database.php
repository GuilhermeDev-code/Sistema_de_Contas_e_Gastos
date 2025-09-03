<?php
/**
 * Configuração do banco de dados para o sistema de gerenciamento de rendas da serralheria
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'serralheria_rendas';
    private $username = 'root';
    private $password = '';
    private $conn;

    /**
     * Conecta ao banco de dados
     */
    public function connect() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            echo "Erro de conexão: " . $e->getMessage();
        }
        
        return $this->conn;
    }
}
?>

