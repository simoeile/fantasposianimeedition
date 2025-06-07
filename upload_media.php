<?php
// --- CONFIGURAZIONE CORS (dal codice precedente, assicurati che sia all'inizio del file) ---
header("Access-Control-Allow-Origin: https://fantasposianimeedition.it");
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Aggiungi POST per l'upload
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit(0);
}

// --- CONFIGURAZIONE WEBDAV ---
$webdavBaseUrl = 'https://storage.fantasposianimeedition.it/remote.php/dav/files/simone/';
$webdavUser = 'simone'; // <-- SOSTITUISCI CON IL TUO USERNAME WEBDAV
$webdavPass = 'Ch@rger01!'; // <-- SOSTITUISCI CON LA TUA PASSWORD WEBDAV

// --- GESTIONE DELL'UPLOAD DAL FRONTTEND ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Controlla se è stato inviato un file
    if (!isset($_FILES['mediaFile']) || $_FILES['mediaFile']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Nessun file caricato o errore di upload.']);
        exit();
    }

    $uploadedFile = $_FILES['mediaFile'];
    $originalFileName = basename($uploadedFile['name']); // Ottieni il nome originale del file
    $tempFilePath = $uploadedFile['tmp_name']; // Percorso temporaneo del file caricato sul server PHP

    // Costruisci l'URL completo per il file sul server WebDAV
    // Il nome del file sul server WebDAV sarà lo stesso del nome originale
    $webdavTargetUrl = $webdavBaseUrl . rawurlencode($originalFileName);

    // Dati di autenticazione Basic
    $auth = base64_encode("$webdavUser:$webdavPass");

    // Prepara il contesto per la richiesta HTTP (necessario per PUT con PHP stream)
    $options = [
        'http' => [
            'method' => 'PUT',
            'header' => "Authorization: Basic $auth\r\n" .
                        "Content-Type: " . $uploadedFile['type'] . "\r\n", // Imposta il Content-Type corretto
            'content' => file_get_contents($tempFilePath), // Leggi il contenuto del file temporaneo
            'ignore_errors' => true // Importante per leggere la risposta in caso di errore HTTP
        ]
    ];

    $context = stream_context_create($options);

    // Esegui la richiesta PUT al server WebDAV
    $response = file_get_contents($webdavTargetUrl, false, $context);

    // Controlla la risposta e gli header HTTP
    $http_status = $http_response_header[0]; // La prima riga degli header contiene lo status HTTP

    // Analizza lo status HTTP
    if (strpos($http_status, '201 Created') !== false || strpos($http_status, '200 OK') !== false || strpos($http_status, '204 No Content') !== false) {
        // Upload riuscito (201 Created, 200 OK, 204 No Content sono tipici per PUT)
        http_response_code(200);
        echo json_encode(['message' => 'Media caricato con successo su WebDAV!', 'url' => $webdavTargetUrl]);
    } else {
        // Errore durante l'upload
        error_log("WebDAV upload failed: $http_status - Response: $response");
        http_response_code(500);
        echo json_encode(['error' => 'Errore nel caricamento del media su WebDAV.', 'details' => $http_status, 'response' => $response]);
    }

    // Rimuovi il file temporaneo dopo l'upload
    @unlink($tempFilePath); // Il @ sopprime gli errori se il file non esiste per qualche motivo

} else {
    http_response_code(405); // Metodo non consentito
    echo json_encode(['error' => 'Metodo non consentito. Usa POST per caricare i file.']);
}
?>