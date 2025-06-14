<?php
header('Content-Type: application/json');
$dir = __DIR__ . '/uploads/' . basename($_POST['team']);
if (!is_dir($dir)) mkdir($dir, 0755, true);

if (!isset($_FILES['file'])) {
  echo json_encode(['success' => false, 'error' => 'Nessun file ricevuto']);
  exit;
}

$f = $_FILES['file'];
$dst = $dir . '/' . bin2hex(random_bytes(8)) . '-' . basename($f['name']);
if (!move_uploaded_file($f['tmp_name'], $dst)) {
  echo json_encode(['success' => false, 'error' => 'Upload fallito']);
  exit;
}

$descr = $_POST['descrizione'] ?? '';
$punt = intval($_POST['punteggio'] ?? 0);
$meta = [
  'descrizione' => $descr,
  'punteggio' => $punt
];
file_put_contents($dst . '.json', json_encode($meta));

$url = '/uploads/' . basename($_POST['team']) . '/' . basename($dst);
echo json_encode(['success' => true, 'url' => $url]);
