<?php 
  if(isset($_GET['sicuro']))
  {
    session_start();
    require 'conn.php';
    $sqlquery="SELECT hash FROM stazioni";
    $risu=$conn->query($sqlquery);
    // Popolo la tabella 'dati_appoggio'
    while($riga=$risu->fetch_assoc())
    {
      $hash=$riga['hash'];
      // Inserisco la media dei valori
      $query="INSERT INTO dati_appoggio(data,ora,temperatura,umidita,pressione,codiceStazione)
              (SELECT dati.data, CONCAT( RIGHT(CONCAT('00',HOUR(dati.ora)),2),':00:00') as ORA, AVG(dati.temperatura) AS TEMPERATURA, AVG(dati.umidita) AS UMIDITA, AVG(dati.pressione) AS PRESSIONE, dati.codiceStazione
              FROM dati
              WHERE dati.codiceStazione='$hash'
              GROUP BY dati.data, HOUR(dati.ora)
              ORDER BY dati.data  ASC)";
      $risultato=$conn->query($query);
      // Inserisco il valore massimo
      $query="INSERT INTO dati_appoggio(data,ora,temperatura,umidita,pressione,codiceStazione)
              (SELECT dati.data, CONCAT( RIGHT(CONCAT('00',HOUR(dati.ora)),2),':00:00') as ORA, MAX(dati.temperatura) AS TEMPERATURA, MAX(dati.umidita) AS UMIDITA, MAX(dati.pressione) AS PRESSIONE, dati.codiceStazione
              FROM dati
              WHERE dati.codiceStazione='$hash'
              GROUP BY dati.data, HOUR(dati.ora)
              ORDER BY dati.data  ASC)";
      $risultato=$conn->query($query);
      // Inserisco il valore minimo
      $query="INSERT INTO dati_appoggio(data,ora,temperatura,umidita,pressione,codiceStazione)
              (SELECT dati.data, CONCAT( RIGHT(CONCAT('00',HOUR(dati.ora)),2),':00:00') as ORA, MIN(dati.temperatura) AS TEMPERATURA, MIN(dati.umidita) AS UMIDITA, MIN(dati.pressione) AS PRESSIONE, dati.codiceStazione
              FROM dati
              WHERE dati.codiceStazione='$hash'
              GROUP BY dati.data, HOUR(dati.ora)
              ORDER BY dati.data  ASC)";
      $risultato=$conn->query($query);
    }
    // Elimino tutti i dati presenti nella tabella 'dati'
    $query="TRUNCATE TABLE dati";
    $risultato=$conn->query($query);

    // Popolo la tabella 'dati' con le nuove righe
    $query="INSERT INTO dati(data,ora,temperatura,umidita,pressione,codiceStazione)
            (SELECT dati_appoggio.data,dati_appoggio.ora,dati_appoggio.temperatura,dati_appoggio.umidita,dati_appoggio.pressione,dati_appoggio.codiceStazione
            FROM dati_appoggio
            ORDER BY dati_appoggio.data  ASC)";
    $risultato=$conn->query($query);

    // Elimina tutti i dati nella tabella di appoggio: 'dati_appoggio'
    $query="TRUNCATE TABLE dati_appoggio";
    $risultato=$conn->query($query);

    $conn->close();
  	echo "Operazione eseguita CORRETTAMENTE";
  }
  else
  	echo "Operazione non eseguita";

?>