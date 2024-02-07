<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header('Access-Control-Allow-Methods: POST');
header("Content-Type: application/json; charset=UTF-8");

function sendResponse($status, $message, $data = null) {
    $response = [
        "status" => $status,
        "message" => $message,
    ];

    if (!is_null($data)) {
        $response["result"] = $data;
    }

    echo json_encode($response);
    exit;
}

$request = json_decode(file_get_contents('php://input'), true);

if (!isset($request['cidade'])) {
    sendResponse(false, 'Cidade de conexão não informada!');
}

if (!isset($request['query'])) {
    sendResponse(false, 'Query SQL de execução não informada!');
}

$forbiddenCommands = ['delete', 'insert', 'update'];
if (preg_match('/\b('.implode('|', $forbiddenCommands).')\b/i', $request['query'])) {
    sendResponse(false, 'Query SQL não pode ser executada, comando proibido!');
}

$hostname = "{$request['cidade']['host']}:{$request['cidade']['database']}";

$conect = ibase_connect($hostname, $request['cidade']['user_db'], $request['cidade']['password_db']);
if (!$conect) {
    sendResponse(false, 'Erro de conexão com filial!', ['error' => ibase_errmsg()]);
}

$query = ibase_query($conect, $request['query']);
if (!$query) {
    sendResponse(false, 'Erro ao executar a consulta!', ['error' => ibase_errmsg()]);
}

$queryResult = ibase_fetch_assoc($query);

sendResponse(true, 'Consulta realizada!', $queryResult);
