<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit;
}

if (!isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'message' => 'Nessun file caricato']);
    exit;
}

$localPath = $_FILES['file']['tmp_name'];
$remoteFileName = basename($_FILES['file']['name']);
$webdavUrl = 'https://storage.fantasposianimeedition.it/remote.php/dav/files/simone/' . $remoteFileName;

$username = 'simone';
$password = 'Ch@rger01!';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webdavUrl);
curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
curl_setopt($ch, CURLOPT_PUT, true);
curl_setopt($ch, CURLOPT_INFILE, fopen($localPath, 'r'));
curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localPath));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
    echo json_encode(['success' => true, 'file' => $remoteFileName]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Errore upload',
        'httpCode' => $httpCode,
        'curlError' => $error
    ]);
}
?>