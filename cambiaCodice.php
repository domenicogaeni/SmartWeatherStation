<?php
	setcookie("codiceStazione", "", time()-3600);
	header("location: login.php");
    die();
?>