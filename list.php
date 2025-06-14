<?php
header('Content-Type: application/json');
$team = basename($_GET['team'] ?? '');
$dir = __DIR__ . '/uploads/' . $team;
$out = [];

if (is_dir($dir)) {
  $files = array_diff(scandir($dir), ['.', '..']);
  foreach ($files as $file) {
    if (preg_match('/\.(jpg|jpeg|png|gif|mp4|webm)$/i', $file)) {
      $metaFile = $dir . '/' . $file . '.json';
      $meta = file_exists($metaFile) ? json_decode(file_get_contents($metaFile), true) : ['descrizione'=>'','punteggio'=>0];
      $out[] = [
        'url' => '/uploads/' . $team . '/' . $file,
        'type' => mime_content_type($dir . '/' . $file),
        'descrizione' => $meta['descrizione'],
        'punteggio' => $meta['punteggio']
      ];
    }
  }
}
echo json_encode($out);
