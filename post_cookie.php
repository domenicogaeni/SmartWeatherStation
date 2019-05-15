<?php
	
    $codice =$_POST['codice'];
    setcookie("codice", $codice, time() + (86400 * 30), "/"); // 86400 = 1 day
    header("location: /prove_cookie.php");

?>