<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../src/Servico.php';

$servico = new Servico();
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            // Buscar serviço específico
            $resultado = $servico->buscarPorId($_GET['id']);
            if($resultado) {
                echo json_encode($resultado);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Serviço não encontrado']);
            }
        } elseif(isset($_GET['cliente_id'])) {
            // Listar serviços por cliente
            $resultado = $servico->listarPorCliente($_GET['cliente_id']);
            echo json_encode($resultado);
        } elseif(isset($_GET['status'])) {
            // Listar serviços por status
            $resultado = $servico->listarPorStatus($_GET['status']);
            echo json_encode($resultado);
        } elseif(isset($_GET['relatorio']) && $_GET['relatorio'] == 'periodo') {
            // Relatório de rendas por período
            $data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
            $data_fim = $_GET['data_fim'] ?? date('Y-m-t');
            $resultado = $servico->calcularRendasPorPeriodo($data_inicio, $data_fim);
            echo json_encode($resultado);
        } else {
            // Listar todos os serviços
            $resultado = $servico->listar();
            echo json_encode($resultado);
        }
        break;
        
    case 'POST':
        // Criar novo serviço
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->cliente_id) && !empty($data->descricao) && !empty($data->valor)) {
            $servico->cliente_id = $data->cliente_id;
            $servico->descricao = $data->descricao;
            $servico->valor = $data->valor;
            $servico->data_servico = $data->data_servico ?? date('Y-m-d');
            $servico->status = $data->status ?? 'Pendente';
            $servico->observacoes = $data->observacoes ?? '';
            
            $id = $servico->criar();
            if($id) {
                http_response_code(201);
                echo json_encode(['message' => 'Serviço criado com sucesso', 'id' => $id]);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Erro ao criar serviço']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Cliente, descrição e valor são obrigatórios']);
        }
        break;
        
    case 'PUT':
        // Atualizar serviço
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id) && !empty($data->cliente_id) && !empty($data->descricao) && !empty($data->valor)) {
            $servico->id = $data->id;
            $servico->cliente_id = $data->cliente_id;
            $servico->descricao = $data->descricao;
            $servico->valor = $data->valor;
            $servico->data_servico = $data->data_servico;
            $servico->status = $data->status;
            $servico->observacoes = $data->observacoes ?? '';
            
            if($servico->atualizar()) {
                echo json_encode(['message' => 'Serviço atualizado com sucesso']);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Erro ao atualizar serviço']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'ID, cliente, descrição e valor são obrigatórios']);
        }
        break;
        
    case 'DELETE':
        // Excluir serviço
        if(isset($_GET['id'])) {
            if($servico->excluir($_GET['id'])) {
                echo json_encode(['message' => 'Serviço excluído com sucesso']);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Erro ao excluir serviço']);
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

