<?php
declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

function getMessage($result, $status = 0, $message = "OK") {
    return [
        'date' => gmdate('Y-m-d H:i:s'),
        'status' => $status,
        'message' => $message,
        'object' => $result,
    ];
}

function selectOne($db, $query, $id) {
    // Prepara e executa  query
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Recupera o resultado
    $result = $stmt->fetch();

    // Verifica se existem dados
    if (!$result) {
        return getMessage(null, 100, 'Dados não encontrados para o id: ' . $id);
    }

    return getMessage($result);
}

function selectAll($db, $query, $pagina) {
    // Prepara e executa  query
    $stmt = $db->prepare($query);
    $stmt->bindValue(':pagina', $pagina, PDO::PARAM_INT);
    $stmt->execute();

    // Recupera o resultado
    $result = $stmt->fetchAll();

    return getMessage($result ? $result : []);
}

function insertOne($db, $query, $params) {
    // Prepara e executa  query
    $stmt = $db->prepare($query);
    $stmt->execute($params);

    // Verifica o numero de linhas inseridas
    if (!$stmt->rowCount()) {
        return getMessage(null, 200, 'Erro ao inserir o id: ' . $params['id']);
    }

    // Recupera o id do ultimo registro inserido
    $params['id'] = $db->lastInsertId();

    // Verifica se existem dados
    return getMessage($params);
}

function updateOne($db, $query, $params) {
    // Prepara e executa  query
    $stmt = $db->prepare($query);
    $stmt->execute($params);

    // Verifica o numero de linhas atualizadas
    if (!$stmt->rowCount()) {
        return getMessage(null, 300, 'Erro ao atualizar o id: ' . $params['id']);
    }

    return getMessage($params);
}

function deleteOne($db, $query, $id) {
    // Prepara e executa  query
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Verifica o numero de linhas deletadas
    if (!$stmt->rowCount()) {
        return getMessage(null, 400, 'Erro ao excluir o id: ' . $id);
    }

    return getMessage(null);
}

// Pegar todos os produtos
    $app->get('/produtos', function (Request $request, Response $response, array $args) use ($container) {
        $query = "SELECT * FROM produtos;
        return $response->withJson(selectAll($this->db1, $query));
    });
// Pegar um produto
    $app->get('/produtos/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "SELECT * FROM produtos WHERE id = :id";
        return $response->withJson(selectOne($this->db1, $query, $args['id']));
    });

// Adicionar novo produto
    $app->post('/produtos/new, function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            INSERT INTO produtos
            (
                id,
		label,
		imgOpcao,
		rating,
		price,
		idStore,
            )
            VALUES (NULL,
		:label,
		:imgOpcao,
		:rating,
		:price,
		:idStore,
            )        
        ";
        $body = $request->getParsedBody();
        return $response->withJson(insertOne($this->db1, $query, $body));
    });

    // update produto
    $app->put('/produtos', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            UPDATE produtos SET
                id = :id,
                label= :label,
                imgOpcao = :imgOpcao,
                rating = :rating,
                email = :email,
                price = :price,
                idStore = :idStore
            WHERE
                id = :id
        ";
        $body = $request->getParsedBody();
        unset($body['produtos']);
        return $response->withJson(updateOne($this->db1, $query, $body));
    });

    // DELETE
    $app->delete('/produtos/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "DELETE FROM produtos WHERE id = :id";
        return $response->withJson(deleteOne($this->db1, $query, intval($args['id'])));
    });
};
