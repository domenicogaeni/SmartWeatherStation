<?php
	require 'conn.php';    
    session_start();
    $codice = mysqli_real_escape_string($conn,$_POST['codice']);
    // Controllo che il codice esiste
    $query="SELECT * FROM `stazioni` WHERE codice='$codice'";
    $risultato=$conn->query($query);
	if(mysqli_num_rows($risultato)=='0')
    {
    	$_SESSION['messaggio']="<div class='alert alert-danger'>Il codice che hai inserito non esiste</div>";
        header("location: /login.php");	
        die();
    }
    else
    {
    	// Inserisco nei cookie il codice della stazione
        $codiceCookie=hash('sha256',$codice);
		setcookie("codiceStazione", $codiceCookie, time() + (86400 * 30), "/"); // Il cookie rimane salvato per un mese
    	header("location: /home.php");    
    }
    $conn->close();       
?>