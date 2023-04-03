<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header('Access-Control-Allow-Methods: POST');
header("Content-Type: application/json; charset=UTF-8");

$request = json_decode(file_get_contents('php://input'), true);

if (!isset($request['cidade'])) {
    echo json_encode([
        "status" => false,
        "message" => 'Cidade de conexão não informada!'
    ]);
    
    return;
}

if (!isset($request['query'])) {
    echo json_encode([
        "status" => false,
        "message" => 'Query SQL de execução não informada!'
    ]);
    return;
}

if (preg_match('~(delete|DELETE|insert|INSERT|update|UPDATE)~', $request['query'])) {
    echo json_encode([
        "status" => false,
        "message" => 'Query SQL não pode ser executada, comando proibido!'
    ]);

    return;
}

$hostname = "{$request['cidade']['host']}:{$request['cidade']['database']}";

if (!($conect = ibase_connect($hostname, $request['cidade']['user_db'], $request['cidade']['password_db']))) {
    echo json_encode([
        "status" => false,
        "message" => 'Erro de conexão com filial!',
        "error" =>  ibase_errmsg()
    ]);

    return;
}

$query = ibase_query($conect, $request['query']);

$queryResult = ibase_fetch_assoc($query);

echo json_encode([
    "status" => true,
    "message" => "Consulta realizada!",
    "result" => $queryResult
]);

return;
