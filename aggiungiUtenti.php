<?php
	require 'conn.php';
    
	if ($_SERVER['REQUEST_METHOD'] == 'GET')
    {
    	$username=mysqli_real_escape_string($conn,$_GET['username']);
        $chatId=mysqli_real_escape_string($conn,$_GET['chatId']);
        $codiceStazione=mysqli_real_escape_string($conn,$_GET['codiceStazione']);
		if($username!=""&&$chatId!=""&&$codiceStazione!="")
        {
        	// Controllo che il codice della stazione esista
            $query="SELECT * FROM `stazioni` WHERE `codice`='$codiceStazione'";
            $risu=$conn->query($query);
            if(mysqli_num_rows($risu)!=0)
            {
           		$hashStazione=hash('sha256',$codiceStazione);
                // Prima controllo che non abbia già i dati
                $query="SELECT * FROM utenti WHERE username='$username' AND chatId='$chatId'";
                $risu=$conn->query($query);
                if(mysqli_num_rows($risu)==0)
                {
                  // Inserisco i dati nel database
                  $query="INSERT INTO utenti (username, chatId,codiceStazione) VALUES ('$username','$chatId','$hashStazione')";
                  $conn->query($query);
                  echo "Ora stai monitorando la tua stazione meteo";
                }
                else
                {
                	$query="UPDATE utenti SET codiceStazione='$hashStazione' WHERE chatId='$chatId'";
                  	$conn->query($query);
                  	echo "Il codice della stazione meteo è stato aggiornato correttamente";
                }
            }
            else
            {
            	echo "Attenzione: Il codice che hai inserito è errato";	
            }
            
        }
    }
?>