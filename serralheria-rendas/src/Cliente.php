<?php
require_once '../config/database.php';

/**
 * Classe para gerenciar clientes da serralheria
 */
class Cliente {
    private $conn;
    private $table = 'clientes';

    public $id;
    public $nome;
    public $telefone;
    public $email;
    public $endereco;
    public $data_cadastro;
    public $ativo;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Criar novo cliente
     */
    public function criar() {
        $query = "INSERT INTO " . $this->table . " 
                  (nome, telefone, email, endereco) 
                  VALUES (:nome, :telefone, :email, :endereco)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar dados
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->endereco = htmlspecialchars(strip_tags($this->endereco));
        
        // Bind dos parâmetros
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':telefone', $this->telefone);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':endereco', $this->endereco);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    /**
     * Listar todos os clientes ativos
     */
    public function listar() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE ativo = 1 
                  ORDER BY nome ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Buscar cliente por ID
     */
    public function buscarPorId($id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE id = :id AND ativo = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Atualizar cliente
     */
    public function atualizar() {
        $query = "UPDATE " . $this->table . " 
                  SET nome = :nome, telefone = :telefone, 
                      email = :email, endereco = :endereco 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar dados
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->endereco = htmlspecialchars(strip_tags($this->endereco));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind dos parâmetros
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':telefone', $this->telefone);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':endereco', $this->endereco);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    /**
     * Excluir cliente (soft delete)
     */
    public function excluir($id) {
        $query = "UPDATE " . $this->table . " 
                  SET ativo = 0 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Buscar clientes por nome
     */
    public function buscarPorNome($nome) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE nome LIKE :nome AND ativo = 1 
                  ORDER BY nome ASC";
        
        $stmt = $this->conn->prepare($query);
        $nome = "%{$nome}%";
        $stmt->bindParam(':nome', $nome);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>

