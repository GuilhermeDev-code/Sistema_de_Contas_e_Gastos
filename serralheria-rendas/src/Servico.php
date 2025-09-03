<?php
require_once '../config/database.php';

/**
 * Classe para gerenciar serviços da serralheria
 */
class Servico {
    private $conn;
    private $table = 'servicos';

    public $id;
    public $cliente_id;
    public $descricao;
    public $valor;
    public $data_servico;
    public $status;
    public $observacoes;
    public $data_cadastro;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Criar novo serviço
     */
    public function criar() {
        $query = "INSERT INTO " . $this->table . " 
                  (cliente_id, descricao, valor, data_servico, status, observacoes) 
                  VALUES (:cliente_id, :descricao, :valor, :data_servico, :status, :observacoes)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar dados
        $this->cliente_id = htmlspecialchars(strip_tags($this->cliente_id));
        $this->descricao = htmlspecialchars(strip_tags($this->descricao));
        $this->valor = htmlspecialchars(strip_tags($this->valor));
        $this->data_servico = htmlspecialchars(strip_tags($this->data_servico));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));
        
        // Bind dos parâmetros
        $stmt->bindParam(':cliente_id', $this->cliente_id);
        $stmt->bindParam(':descricao', $this->descricao);
        $stmt->bindParam(':valor', $this->valor);
        $stmt->bindParam(':data_servico', $this->data_servico);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':observacoes', $this->observacoes);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    /**
     * Listar todos os serviços com informações do cliente
     */
    public function listar() {
        $query = "SELECT s.*, c.nome as cliente_nome 
                  FROM " . $this->table . " s 
                  INNER JOIN clientes c ON s.cliente_id = c.id 
                  WHERE c.ativo = 1 
                  ORDER BY s.data_servico DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Listar serviços por cliente
     */
    public function listarPorCliente($cliente_id) {
        $query = "SELECT s.*, c.nome as cliente_nome 
                  FROM " . $this->table . " s 
                  INNER JOIN clientes c ON s.cliente_id = c.id 
                  WHERE s.cliente_id = :cliente_id AND c.ativo = 1 
                  ORDER BY s.data_servico DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cliente_id', $cliente_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Buscar serviço por ID
     */
    public function buscarPorId($id) {
        $query = "SELECT s.*, c.nome as cliente_nome 
                  FROM " . $this->table . " s 
                  INNER JOIN clientes c ON s.cliente_id = c.id 
                  WHERE s.id = :id AND c.ativo = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Atualizar serviço
     */
    public function atualizar() {
        $query = "UPDATE " . $this->table . " 
                  SET cliente_id = :cliente_id, descricao = :descricao, 
                      valor = :valor, data_servico = :data_servico, 
                      status = :status, observacoes = :observacoes 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar dados
        $this->cliente_id = htmlspecialchars(strip_tags($this->cliente_id));
        $this->descricao = htmlspecialchars(strip_tags($this->descricao));
        $this->valor = htmlspecialchars(strip_tags($this->valor));
        $this->data_servico = htmlspecialchars(strip_tags($this->data_servico));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind dos parâmetros
        $stmt->bindParam(':cliente_id', $this->cliente_id);
        $stmt->bindParam(':descricao', $this->descricao);
        $stmt->bindParam(':valor', $this->valor);
        $stmt->bindParam(':data_servico', $this->data_servico);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':observacoes', $this->observacoes);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    /**
     * Excluir serviço
     */
    public function excluir($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Calcular total de rendas por período
     */
    public function calcularRendasPorPeriodo($data_inicio, $data_fim) {
        $query = "SELECT 
                    COUNT(*) as total_servicos,
                    SUM(valor) as total_valor,
                    AVG(valor) as valor_medio
                  FROM " . $this->table . " 
                  WHERE data_servico BETWEEN :data_inicio AND :data_fim";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':data_inicio', $data_inicio);
        $stmt->bindParam(':data_fim', $data_fim);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Listar serviços por status
     */
    public function listarPorStatus($status) {
        $query = "SELECT s.*, c.nome as cliente_nome 
                  FROM " . $this->table . " s 
                  INNER JOIN clientes c ON s.cliente_id = c.id 
                  WHERE s.status = :status AND c.ativo = 1 
                  ORDER BY s.data_servico DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>

