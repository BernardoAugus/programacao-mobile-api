<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;


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

function searchAll($db, $query, $pagina, $titulo) {
    // Prepara e executa  query
    $stmt = $db->prepare($query);
    $stmt->bindValue(':pagina', $pagina, PDO::PARAM_INT);
    $stmt->bindValue(':titulo', $titulo, PDO::PARAM_STR);
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

function deleteInscricao($db, $query, $id_usuario, $id_palestra) {
    // Prepara e executa  query
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->bindValue(':id_palestra', $id_palestra, PDO::PARAM_INT);
    $stmt->execute();

    // Verifica o numero de linhas deletadas
    if (!$stmt->rowCount()) {
        return getMessage(null, 600, 'Erro ao excluir o inscricao');
    }

    return getMessage(null);
}


return function (App $app) {
    $container = $app->getContainer();

    $app->get('/', function (Request $request, Response $response, array $args) use ($container) {
        $container->get('logger')->info("Slim-Skeleton '/' route");
        return "Hello, world!";
    });

    // ------------------------------------------------------------------------
    //region CURSOS
    // ------------------------------------------------------------------------

    // SELECT
    $app->get('/tecplus/curso/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "SELECT * FROM curso WHERE id = :id";
        return $response->withJson(selectOne($this->db1, $query, $args['id']));
    });

    // SELECT ALL
    $app->get('/tecplus/curso/all/{pagina}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "SELECT * FROM curso LIMIT 7 OFFSET :pagina";
        $pagina = intval($args['pagina']) * 7;
        return $response->withJson(selectAll($this->db1, $query, $pagina));
    });

    //endregion

    // ------------------------------------------------------------------------
    //region CURSO DE EXTENSAO
    // ------------------------------------------------------------------------

    // SELECT
    $app->get('/tecplus/curso_extensao/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "SELECT * FROM curso_extensao WHERE id = :id";
        return $response->withJson(selectOne($this->db1, $query, $args['id']));
    });

    // SELECT ALL
    $app->get('/tecplus/curso_extensao/all/{pagina}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "SELECT * FROM curso_extensao LIMIT 7 OFFSET :pagina";
        $pagina = intval($args['pagina']) * 7;
        return $response->withJson(selectAll($this->db1, $query, $pagina));
    });



    // INSERT
    $app->post('/tecplus/curso_extensao/new', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            INSERT INTO curso_extensao
            (
                id,
                titulo,
                descricao,
                periodo,
                valor,
                `local`,
                local_url,
                foto_url
            )
            VALUES (NULL,
                :titulo,
                :descricao,
                :periodo,
                :valor,
                :local,
                :local_url,
                :foto_url
            )        
        ";
        $body = $request->getParsedBody();
        return $response->withJson(insertOne($this->db1, $query, $body));
    });

    // UPDATE
    $app->put('/tecplus/curso_extensao', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            UPDATE curso_extensao SET
                titulo = :titulo,
                descricao = :descricao,
                periodo = :periodo,
                valor = :valor,
                `local` = :local,
                local_url = :local_url,
                foto_url = :foto_url
            WHERE
                id = :id
        ";
        $body = $request->getParsedBody();
        return $response->withJson(updateOne($this->db1, $query, $body));
    });

    // DELETE
    $app->delete('/tecplus/curso_extensao/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "DELETE FROM curso_extensao WHERE id = :id";
        return $response->withJson(deleteOne($this->db1, $query, intval($args['id'])));
    });

    //endregion

    // ------------------------------------------------------------------------
    //region INSTITUICAO
    // ------------------------------------------------------------------------

    // SELECT
    $app->get('/tecplus/instituicao', function (Request $request, Response $response, array $args) use ($container) {
        $query = "SELECT * FROM instituicao WHERE id = :id";
        return $response->withJson(selectOne($this->db1, $query, 0));
    });

    // UPDATE
    $app->put('/tecplus/instituicao', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            UPDATE instituicao SET
                historia = :historia,
                localizacao = :localizacao,
                mapa = :mapa,
                contato_email = :contato_email,
                contato_telefone = :contato_telefone
            WHERE
                id = 0
        ";
        $body = $request->getParsedBody();
        return $response->withJson(updateOne($this->db1, $query, $body));
    });

    //endregion

    // ------------------------------------------------------------------------
    //region PALESTRANTE
    // ------------------------------------------------------------------------

    // SELECT
    $app->get('/tecplus/palestrante/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "SELECT * FROM palestrante WHERE id = :id";
        return $response->withJson(selectOne($this->db1, $query, $args['id']));
    });

    // SELECT ALL
    $app->get('/tecplus/palestrante/all/{pagina}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "SELECT * FROM palestrante LIMIT 7 OFFSET :pagina";
        $pagina = intval($args['pagina']) * 7;
        return $response->withJson(selectAll($this->db1, $query, $pagina));
    });

    // INSERT
    $app->post('/tecplus/palestrante/new', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            INSERT INTO palestrante
            (
                id,
                nome,
                biografia,
                foto_url
            )
            VALUES (NULL,
                :nome,
                :biografia,
                :foto_url
            )        
        ";
        $body = $request->getParsedBody();
        return $response->withJson(insertOne($this->db1, $query, $body));
    });

    // UPDATE
    $app->put('/tecplus/palestrante', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            UPDATE palestrante SET
                nome = :nome,
                biografia = :biografia,
                foto_url = :foto_url
            WHERE
                id = :id
        ";
        $body = $request->getParsedBody();
        return $response->withJson(updateOne($this->db1, $query, $body));
    });

    // DELETE
    $app->delete('/tecplus/palestrante/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "DELETE FROM palestrante WHERE id = :id";
        return $response->withJson(deleteOne($this->db1, $query, intval($args['id'])));
    });

    //endregion

    // ------------------------------------------------------------------------
    //region PALESTRA
    // ------------------------------------------------------------------------

    // SELECT
    $app->get('/tecplus/palestra/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "SELECT * FROM palestra WHERE id = :id";
        $palestra = selectOne($this->db1, $query, $args['id']);

        if (!$palestra['status']) {
            $query = "SELECT * FROM palestrante WHERE id = :id";
            $palestrante = selectOne($this->db1, $query, $palestra['object']['id_palestrante']);

            $query = "SELECT count(1) as num_inscritos FROM inscricao WHERE id_palestra = :id";
            $vagas = selectOne($this->db1, $query, $args['id']);

            $palestra['object']['palestrante'] = $palestrante['object'];
            $palestra['object']['num_inscritos'] = $vagas['object']['num_inscritos'];
        }

        return $response->withJson($palestra);
    });

    // SELECT ALL
    $app->get('/tecplus/palestra/all/{pagina}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            SELECT p.*
                 , pa.nome
                 , pa.biografia
                 , pa.foto_url
                 , (SELECT count(1) 
                    FROM inscricao i 
                    WHERE i.id_palestra = p.id) as num_inscritos 
            FROM palestra p 
                INNER JOIN palestrante pa ON pa.id = p.id_palestrante
            LIMIT 7 
            OFFSET :pagina";
        $pagina = intval($args['pagina']) * 7;
        $palestras = selectAll($this->db1, $query, $pagina);

        foreach ($palestras['object'] as &$item) {
            $item['palestrante'] = [
                'nome' => $item['nome'],
                'biografia' => $item['biografia'],
                'foto_url' => $item['foto_url'],
            ];
            unset($item['nome']);
            unset($item['biografia']);
            unset($item['foto_url']);
            unset($item['id_palestrante']);
        }
        unset($item);

        return $response->withJson($palestras);
    });

    // SEARCH ALL
    $app->get('/tecplus/palestra/search/{pagina}/{titulo}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            SELECT p.*
                 , pa.nome
                 , pa.biografia
                 , pa.foto_url
                 , (SELECT count(1) 
                    FROM inscricao i 
                    WHERE i.id_palestra = p.id) as num_inscritos 
            FROM palestra p 
                INNER JOIN palestrante pa ON pa.id = p.id_palestrante
            WHERE upper(p.titulo) LIKE upper(:titulo) or upper(p.descricao) LIKE upper(:titulo)
            LIMIT 7 
            OFFSET :pagina";
        $pagina = intval($args['pagina']) * 7;
        $titulo = '%'.$args['titulo'].'%';
        $palestras = searchAll($this->db1, $query, $pagina, $titulo);

        foreach ($palestras['object'] as &$item) {
            $item['palestrante'] = [
                'nome' => $item['nome'],
                'biografia' => $item['biografia'],
                'foto_url' => $item['foto_url'],
            ];
            unset($item['nome']);
            unset($item['biografia']);
            unset($item['foto_url']);
            unset($item['id_palestrante']);
        }
        unset($item);

        return $response->withJson($palestras);
    });


    // INSERT
    $app->post('/tecplus/palestra/new', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            INSERT INTO palestra
            (
                id,
                titulo,
                descricao,
                numero_vagas,
                foto_url,
                local,
                data,
                id_palestrante
            )
            VALUES (NULL,
                :titulo,
                :descricao,
                :numero_vagas,
                :foto_url,
                :local,
                :data,
                :id_palestrante
            )
        ";
        $body = $request->getParsedBody();
        $body['id_palestrante'] = $body['palestrante']['id'];
        unset($body['palestrante']);
        return $response->withJson(insertOne($this->db1, $query, $body));
    });

    // UPDATE
    $app->put('/tecplus/palestra', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            UPDATE palestra SET
                titulo = :titulo,
                descricao = :descricao,
                numero_vagas = :numero_vagas,
                foto_url = :foto_url,
                local= :local,
                data = :data,
                id_palestrante = :id_palestrante
            WHERE
                id = :id
        ";
        $body = $request->getParsedBody();
        $body['id_palestrante'] = $body['palestrante']['id'];
        unset($body['palestrante']);
        return $response->withJson(updateOne($this->db1, $query, $body));
    });

    // DELETE
    $app->delete('/tecplus/palestra/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "DELETE FROM palestra WHERE id = :id";
        return $response->withJson(deleteOne($this->db1, $query, intval($args['id'])));
    });

    //endregion

    // ------------------------------------------------------------------------
    //region USUÁRIO
    // ------------------------------------------------------------------------

    // SELECT
    $app->get('/tecplus/usuario/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            SELECT *, (SELECT nome FROM curso where id = id_curso) as curso 
            FROM usuario 
            WHERE id = :id ";
        $usuario = selectOne($this->db1, $query, $args['id']);

        if (!$usuario['status']) {
            $query = "
                SELECT p.id
                     , p.titulo
                     , p.descricao
                     , p.foto_url
                     , p.local
                     , p.data 
                     , i.compareceu 
                FROM inscricao i
                    INNER JOIN palestra p ON i.id_palestra = p.id
                WHERE id_usuario = :pagina";
            $palestras = selectAll($this->db1, $query, $usuario['object']['id']);
            $usuario['object']['palestras'] = $palestras['object'];
        }

        return $response->withJson($usuario);
    });

    // SELECT ALL
    $app->get('/tecplus/usuario/all/{pagina}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            SELECT *, (SELECT nome FROM curso where id = id_curso) as curso
            FROM usuario
            LIMIT 7 
            OFFSET :pagina";
        $pagina = intval($args['pagina']) * 7;
        return $response->withJson(selectAll($this->db1, $query, $pagina));
    });

    // INSERT
    $app->post('/tecplus/usuario/new', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            INSERT INTO usuario
            (
                id,
                matricula,
                nome,
                sobrenome,
                email,
                id_curso,
                foto_url,
                tipo,
                token_facebook,
                token_google
            )
            VALUES (NULL,
                :matricula,
                :nome,
                :sobrenome,
                :email,
                :id_curso,
                :foto_url,
                :tipo,
                :token_facebook,
                :token_google
            )
        ";
        $body = $request->getParsedBody();
        unset($body['id']);
        unset($body['curso']);
        return $response->withJson(insertOne($this->db1, $query, $body));
    });

    // UPDATE
    $app->put('/tecplus/usuario', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            UPDATE usuario SET
                id = :id,
                matricula = :matricula,
                nome = :nome,
                sobrenome = :sobrenome,
                email = :email,
                id_curso = :id_curso,
                foto_url = :foto_url,
                tipo = :tipo,
                token_facebook = :token_facebook,
                token_google = :token_google
            WHERE
                id = :id
        ";
        $body = $request->getParsedBody();
        unset($body['curso']);
        return $response->withJson(updateOne($this->db1, $query, $body));
    });

    // DELETE
    $app->delete('/tecplus/usuario/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "DELETE FROM usuario WHERE id = :id";
        return $response->withJson(deleteOne($this->db1, $query, intval($args['id'])));
    });

    //endregion

    // ------------------------------------------------------------------------
    //region INSCRICAO
    // ------------------------------------------------------------------------

    // INSERT
    $app->post('/tecplus/inscricao/{id_usuario}/{id_palestra}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            INSERT INTO inscricao
            (
                id,
                id_usuario,
                id_palestra
            )
            VALUES (NULL,
                :id_usuario,
                :id_palestra
            )
        ";
        $body = [
            'id_usuario' => $args['id_usuario'],
            'id_palestra' => $args['id_palestra'],
        ];
        return $response->withJson(insertOne($this->db1, $query, $body));
    });

    // UPDATE
    $app->put('/tecplus/presenca/{id_usuario}/{id_palestra}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            UPDATE inscricao SET
                compareceu = 1,
                data_presenca = CURRENT_TIMESTAMP
            WHERE
                id_usuario = :id_usuario AND
                id_palestra = :id_palestra
        ";
        $body = [
            'id_usuario' => $args['id_usuario'],
            'id_palestra' => $args['id_palestra'],
        ];
        return $response->withJson(insertOne($this->db1, $query, $body));
    });


    // DELETE
    $app->delete('/tecplus/inscricao/{id_usuario}/{id_palestra}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "DELETE FROM inscricao WHERE id_usuario = :id_usuario and id_palestra = :id_palestra";
        return $response->withJson(deleteInscricao($this->db1, $query, intval($args['id_usuario']), intval($args['id_palestra'])));
    });


    //endregion


    // --------------------------------------------------------------------------
    // --------------------------------------------------------------------------
    // --------------------------------------------------------------------------

    // ------------------------------------------------------------------------
    //region USUÁRIO
    // ------------------------------------------------------------------------

    // SELECT
    $app->get('/sustentabilidade/usuario/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            SELECT *
            FROM usuario 
            WHERE id = :id ";
        $usuario = selectOne($this->db3, $query, $args['id']);
        return $response->withJson($usuario);
    });

    // SELECT ALL
    $app->get('/sustentabilidade/usuario/all/{pagina}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            SELECT *
            FROM usuario
            LIMIT 7 
            OFFSET :pagina";
        $pagina = intval($args['pagina']) * 7;
        return $response->withJson(selectAll($this->db3, $query, $pagina));
    });

    // INSERT
    $app->post('/sustentabilidade/usuario/new', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            INSERT INTO usuario
            (
                id,
                matricula,
                nome,
                sobrenome,
                email,
                foto_url,
                tipo,
                token_facebook,
                token_google
            )
            VALUES (NULL,
                :matricula,
                :nome,
                :sobrenome,
                :email,
                :foto_url,
                :tipo,
                :token_facebook,
                :token_google
            )
        ";
        $body = $request->getParsedBody();
        unset($body['id']);
        return $response->withJson(insertOne($this->db3, $query, $body));
    });

    // UPDATE
    $app->put('/sustentabilidade/usuario', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            UPDATE usuario SET
                id = :id,
                matricula = :matricula,
                nome = :nome,
                sobrenome = :sobrenome,
                email = :email,
                foto_url = :foto_url,
                tipo = :tipo,
                token_facebook = :token_facebook,
                token_google = :token_google
            WHERE
                id = :id
        ";
        $body = $request->getParsedBody();
        return $response->withJson(updateOne($this->db3, $query, $body));
    });

    // DELETE
    $app->delete('/sustentabilidade/usuario/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "DELETE FROM usuario WHERE id = :id";
        return $response->withJson(deleteOne($this->db3, $query, intval($args['id'])));
    });

    //endregion

    // ------------------------------------------------------------------------
    //region AGROTOXICO
    // ------------------------------------------------------------------------

    // SELECT
    $app->get('/sustentabilidade/agrotoxico/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            SELECT *
            FROM agrotoxico 
            WHERE id = :id ";
        $item = selectOne($this->db3, $query, $args['id']);
        return $response->withJson($item);
    });

    // SELECT ALL
    $app->get('/sustentabilidade/agrotoxico/all/{pagina}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            SELECT *
            FROM agrotoxico
            LIMIT 7 
            OFFSET :pagina";
        $item = intval($args['pagina']) * 7;
        return $response->withJson(selectAll($this->db3, $query, $item));
    });

    // INSERT
    $app->post('/sustentabilidade/agrotoxico/new', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            INSERT INTO agrotoxico
            (
                id,
                titulo,
                descricao,
                foto
            )
            VALUES (NULL,
                :titulo,
                :descricao,
                :foto
            )
        ";
        $body = $request->getParsedBody();
        unset($body['id']);
        return $response->withJson(insertOne($this->db3, $query, $body));
    });

    // UPDATE
    $app->put('/sustentabilidade/agrotoxico', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            UPDATE agrotoxico SET
                titulo = :titulo,
                descricao = :descricao,
                foto = :foto
            WHERE
                id = :id
        ";
        $body = $request->getParsedBody();
        return $response->withJson(updateOne($this->db3, $query, $body));
    });

    // DELETE
    $app->delete('/sustentabilidade/agrotoxico/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "DELETE FROM agrotoxico WHERE id = :id";
        return $response->withJson(deleteOne($this->db3, $query, intval($args['id'])));
    });

    //endregion


    // ------------------------------------------------------------------------
    //region reciclagem
    // ------------------------------------------------------------------------

    // SELECT
    $app->get('/sustentabilidade/reciclagem/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            SELECT *
            FROM reciclagem 
            WHERE id = :id ";
        $item = selectOne($this->db3, $query, $args['id']);
        return $response->withJson($item);
    });

    // SELECT ALL
    $app->get('/sustentabilidade/reciclagem/all/{pagina}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            SELECT *
            FROM reciclagem
            LIMIT 7 
            OFFSET :pagina";
        $item = intval($args['pagina']) * 7;
        return $response->withJson(selectAll($this->db3, $query, $item));
    });

    // INSERT
    $app->post('/sustentabilidade/reciclagem/new', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            INSERT INTO reciclagem
            (
                id,
                titulo,
                descricao,
                foto
            )
            VALUES (NULL,
                :titulo,
                :descricao,
                :foto
            )
        ";
        $body = $request->getParsedBody();
        unset($body['id']);
        return $response->withJson(insertOne($this->db3, $query, $body));
    });

    // UPDATE
    $app->put('/sustentabilidade/reciclagem', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            UPDATE reciclagem SET
                titulo = :titulo,
                descricao = :descricao,
                foto = :foto
            WHERE
                id = :id
        ";
        $body = $request->getParsedBody();
        return $response->withJson(updateOne($this->db3, $query, $body));
    });

    // DELETE
    $app->delete('/sustentabilidade/reciclagem/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "DELETE FROM reciclagem WHERE id = :id";
        return $response->withJson(deleteOne($this->db3, $query, intval($args['id'])));
    });

    //endregion


    // ------------------------------------------------------------------------
    //region reducao_lixo
    // ------------------------------------------------------------------------

    // SELECT
    $app->get('/sustentabilidade/reducao_lixo/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            SELECT *
            FROM reducao_lixo 
            WHERE id = :id ";
        $item = selectOne($this->db3, $query, $args['id']);
        return $response->withJson($item);
    });

    // SELECT ALL
    $app->get('/sustentabilidade/reducao_lixo/all/{pagina}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            SELECT *
            FROM reducao_lixo
            LIMIT 7 
            OFFSET :pagina";
        $item = intval($args['pagina']) * 7;
        return $response->withJson(selectAll($this->db3, $query, $item));
    });

    // INSERT
    $app->post('/sustentabilidade/reducao_lixo/new', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            INSERT INTO reducao_lixo
            (
                id,
                titulo,
                descricao,
                foto
            )
            VALUES (NULL,
                :titulo,
                :descricao,
                :foto
            )
        ";
        $body = $request->getParsedBody();
        unset($body['id']);
        return $response->withJson(insertOne($this->db3, $query, $body));
    });

    // UPDATE
    $app->put('/sustentabilidade/reducao_lixo', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            UPDATE reducao_lixo SET
                titulo = :titulo,
                descricao = :descricao,
                foto = :foto
            WHERE
                id = :id
        ";
        $body = $request->getParsedBody();
        return $response->withJson(updateOne($this->db3, $query, $body));
    });

    // DELETE
    $app->delete('/sustentabilidade/reducao_lixo/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "DELETE FROM reducao_lixo WHERE id = :id";
        return $response->withJson(deleteOne($this->db3, $query, intval($args['id'])));
    });

    //endregion

    // ------------------------------------------------------------------------
    //region residuo
    // ------------------------------------------------------------------------

    // SELECT
    $app->get('/sustentabilidade/residuo/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            SELECT *
            FROM residuo 
            WHERE id = :id ";
        $item = selectOne($this->db3, $query, $args['id']);
        return $response->withJson($item);
    });

    // SELECT ALL
    $app->get('/sustentabilidade/residuo/all/{pagina}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            SELECT *
            FROM residuo
            LIMIT 7 
            OFFSET :pagina";
        $item = intval($args['pagina']) * 7;
        return $response->withJson(selectAll($this->db3, $query, $item));
    });

    // INSERT
    $app->post('/sustentabilidade/residuo/new', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            INSERT INTO residuo
            (
                id,
                titulo,
                descricao,
                foto
            )
            VALUES (NULL,
                :titulo,
                :descricao,
                :foto
            )
        ";
        $body = $request->getParsedBody();
        unset($body['id']);
        return $response->withJson(insertOne($this->db3, $query, $body));
    });

    // UPDATE
    $app->put('/sustentabilidade/residuo', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            UPDATE residuo SET
                titulo = :titulo,
                descricao = :descricao,
                foto = :foto
            WHERE
                id = :id
        ";
        $body = $request->getParsedBody();
        return $response->withJson(updateOne($this->db3, $query, $body));
    });

    // DELETE
    $app->delete('/sustentabilidade/residuo/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "DELETE FROM residuo WHERE id = :id";
        return $response->withJson(deleteOne($this->db3, $query, intval($args['id'])));
    });

    //endregion


    // ------------------------------------------------------------------------
    //region organico
    // ------------------------------------------------------------------------

    // SELECT
    $app->get('/sustentabilidade/organico/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            SELECT *
            FROM organico 
            WHERE id = :id ";
        $item = selectOne($this->db3, $query, $args['id']);
        return $response->withJson($item);
    });

    // SELECT ALL
    $app->get('/sustentabilidade/organico/all/{pagina}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            SELECT *
            FROM organico
            LIMIT 7 
            OFFSET :pagina";
        $item = intval($args['pagina']) * 7;
        return $response->withJson(selectAll($this->db3, $query, $item));
    });

    // INSERT
    $app->post('/sustentabilidade/organico/new', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            INSERT INTO organico
            (
                id,
                titulo,
                descricao,
                `local`,
                local_url,
                dia,
                horario,
                foto
            )
            VALUES (NULL,
                :titulo,
                :descricao,
                :local,
                :local_url,
                :dia,
                :horario,
                :foto
            )
        ";
        $body = $request->getParsedBody();
        unset($body['id']);
        return $response->withJson(insertOne($this->db3, $query, $body));
    });

    // UPDATE
    $app->put('/sustentabilidade/organico', function (Request $request, Response $response, array $args) use ($container) {
        $query = "
            UPDATE organico SET
                titulo = :titulo,
                descricao = :descricao,
                `local` = :local,
                local_url = :local_url,
                dia = :dia,
                horario = :horario,
                foto = :foto
            WHERE
                id = :id
        ";
        $body = $request->getParsedBody();
        return $response->withJson(updateOne($this->db3, $query, $body));
    });

    // DELETE
    $app->delete('/sustentabilidade/organico/{id}', function (Request $request, Response $response, array $args) use ($container) {
        $query = "DELETE FROM organico WHERE id = :id";
        return $response->withJson(deleteOne($this->db3, $query, intval($args['id'])));
    });

    //endregion



};
