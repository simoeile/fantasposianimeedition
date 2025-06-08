<?php
header('Content-Type: application/json');
$jsonFile = _DIR_ . '/scores.json';
if (file_exists($jsonFile)) {
    $content = file_get_contents($jsonFile);
    echo $content;
} else {
    echo json_encode(new stdClass());
}