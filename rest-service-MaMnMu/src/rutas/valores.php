<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;

$app = AppFactory::create();

$app->get('/public/municipio/{municipio}', function(Request $request, Response $response) {
    $municipio = $request->getAttribute('municipio');
    $sql = "SELECT precio FROM municipios WHERE nombre = '$municipio'";
    try {
        $db = new db();
        $db = $db->conectDB();
        $resultado = $db->query($sql);
        
        if ($resultado->rowCount() > 0) {
            $provincias = $resultado->fetchAll(PDO::FETCH_OBJ);
            $response->getBody()->write(json_encode($provincias));
            return $response;
        } else {
            $response->getBody()->write(json_encode("No existen provincias en la BBDD"));
            return $response;
        }
        $resultado = null;
        $db = null;
    } catch (PDOException $e) {
        $response->getBody()->write('{"error" : {"text":' . $e->getMessage() .'}');
        return $response;
    }
});


