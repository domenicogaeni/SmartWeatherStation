<?php
  require 'conn.php';

  $temperatura=mysqli_real_escape_string($conn,$_GET['temperatura']);
  $umidita=mysqli_real_escape_string($conn,$_GET['umidita']);
  $pressione=mysqli_real_escape_string($conn,$_GET['pressione'])+3000;//+4000;
  $codiceStazione=mysqli_real_escape_string($conn,$_GET['codiceStazione']);
  $data = date("Y-m-d");
  $ora= date("H:i:s");
  $sqlquery="INSERT INTO dati (data, ora, temperatura, umidita, pressione,codiceStazione) VALUES ('$data', '$ora', '$temperatura', '$umidita', '$pressione','$codiceStazione');";
  $result = $conn->query($sqlquery);
  $conn->close();
?>
