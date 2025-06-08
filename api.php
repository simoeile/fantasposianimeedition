<?php
// api.php
$filename = "dati.json";
$data = json_decode(file_get_contents($filename), true) ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!$input) exit;

    $team = $input['team'];
    $newItem = [
        "type" => $input['type'],
        "url" => $input['url'],
        "descrizione" => $input['descrizione'],
        "punteggio" => (int)$input['punteggio']
    ];

    if (!isset($data[$team])) $data[$team] = [];
    $data[$team][] = $newItem;

    file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
    echo json_encode(["success" => true]);
}