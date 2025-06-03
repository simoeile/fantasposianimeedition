<?php
$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["media"]["name"]);
$uploadOk = 1;
$fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Controlla se il file è un'immagine o un video reale
if(isset($_POST["submit"])) {
  $check = getimagesize($_FILES["media"]["tmp_name"]);
  if($check !== false) {
    $uploadOk = 1;
  } else {
    $uploadOk = 1; // Permetti anche i video
  }
}

// Controlla se il file esiste già
if (file_exists($target_file)) {
  echo "Spiacente, il file esiste già.";
  $uploadOk = 0;
}


 
::contentReference[oaicite:19]{index=19}
 
