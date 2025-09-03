<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../src/Cliente.php';

$cliente = new Cliente();
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            // Buscar cliente específico
            $resultado = $cliente->buscarPorId($_GET['id']);
            if($resultado) {
                echo json_encode($resultado);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Cliente não encontrado']);
            }
        } elseif(isset($_GET['busca'])) {
            // Buscar clientes por nome
            $resultado = $cliente->buscarPorNome($_GET['busca']);
            echo json_encode($resultado);
        } else {
            // Listar todos os clientes
            $resultado = $cliente->listar();
            echo json_encode($resultado);
        }
        break;
        
    case 'POST':
        // Criar novo cliente
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->nome)) {
            $cliente->nome = $data->nome;
            $cliente->telefone = $data->telefone ?? '';
            $cliente->email = $data->email ?? '';
            $cliente->endereco = $data->endereco ?? '';
            
            $id = $cliente->criar();
            if($id) {
                http_response_code(201);
                echo json_encode(['message' => 'Cliente criado com sucesso', 'id' => $id]);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Erro ao criar cliente']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Nome é obrigatório']);
        }
        break;
        
    case 'PUT':
        // Atualizar cliente
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id) && !empty($data->nome)) {
            $cliente->id = $data->id;
            $cliente->nome = $data->nome;
            $cliente->telefone = $data->telefone ?? '';
            $cliente->email = $data->email ?? '';
            $cliente->endereco = $data->endereco ?? '';
            
            if($cliente->atualizar()) {
                echo json_encode(['message' => 'Cliente atualizado com sucesso']);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Erro ao atualizar cliente']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'ID e nome são obrigatórios']);
        }
        break;
        
    case 'DELETE':
        // Excluir cliente
        if(isset($_GET['id'])) {
            if($cliente->excluir($_GET['id'])) {
                echo json_encode(['message' => 'Cliente excluído com sucesso']);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Erro ao excluir cliente']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'ID é obrigatório']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['message' => 'Método não permitido']);
        break;
}
?>

