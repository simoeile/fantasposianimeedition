<?php
// Configura la cartella upload
$uploadDir = _DIR_ . '/uploads/';

// Crea cartella uploads se non esiste
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755);
}

// File JSON per i dati
$jsonFile = _DIR_ . '/scores.json';

// Ottieni dati POST
$teamKey = $_POST['teamKey'] ?? '';
$punteggio = intval($_POST['punteggio'] ?? 0);
$descrizione = trim($_POST['descrizione'] ?? '');

if (!$teamKey || !isset($_FILES['immagine'])) {
    echo json_encode(['success' => false, 'message' => 'Dati mancanti']);
    exit;
}

// Cartella specifica team
$teamDir = $uploadDir . $teamKey . '/';
if (!is_dir($teamDir)) {
    mkdir($teamDir, 0755, true);
}

$file = $_FILES['immagine'];

// Controlla upload errori
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Errore upload file']);
    exit;
}

// Estensioni consentite
$allowed = ['jpg','jpeg','png','gif','mp4','webm','mov'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Tipo file non consentito']);
    exit;
}

// Nome file unico
$newFileName = uniqid() . '' . preg_replace('/[^a-zA-Z0-9.-]/', '', $file['name']);
$dest = $teamDir . $newFileName;

// Sposta file caricato
if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['success' => false, 'message' => 'Errore salvataggio file']);
    exit;
}

// URL per accesso pubblico (adatta se server ha URL differente)
$urlPath = 'uploads/' . $teamKey . '/' . $newFileName;

// Leggi dati esistenti
$scores = [];
if (file_exists($jsonFile)) {
    $content = file_get_contents($jsonFile);
    $scores = json_decode($content, true) ?: [];
}

// Aggiungi nuovo record
if (!isset($scores[$teamKey])) {
    $scores[$teamKey] = [];
}

$scores[$teamKey][] = [
    'punteggio' => $punteggio,
    'immagine' => $urlPath,
    'descrizione' => $descrizione
];

// Salva di nuovo JSON
file_put_contents($jsonFile, json_encode($scores, JSON_PRETTY_PRINT));

echo json_encode(['success' => true]);