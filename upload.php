<?php
// Configurazione credenziali WebDAV (nascoste nel server)
$username = "simone";
$password = "Ch@rger01!";
$remoteBase = "https://storage.fantasposianimeedition.it/remote.php/dav/files/simone/";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['media']) || !isset($_POST['descrizione']) || !isset($_POST['punteggio'])) {
        http_response_code(400);
        echo json_encode(["error" => "Dati incompleti."]);
        exit;
    }

    $file = $_FILES['media'];
    $descrizione = $_POST['descrizione'];
    $punteggio = intval($_POST['punteggio']);
    $filename = time() . "_" . basename($file['name']);
    $url = $remoteBase . $filename;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_INFILE, fopen($file['tmp_name'], 'r'));
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file['tmp_name']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: " . $file['type']]);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status >= 200 && $status < 300) {
        echo json_encode([
            "type" => $file['type'],
            "url" => $url,
            "descrizione" => $descrizione,
            "punteggio" => $punteggio
        ]);
    } else {
        http_response_code($status);
        echo json_encode(["error" => "Errore upload WebDAV ($status)"]);
    }
} else {
    http_response_code(405);
    echo "Metodo non consentito";
}
?>
