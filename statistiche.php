<?php
	session_start();
    require 'conn.php';    
    if (!isset($_COOKIE["codiceStazione"]))
    {    	
        $_SESSION['messaggio']="<div class='alert alert-info'>Inserisci il codice della stazione meteo</div>";
    	header("location: /login.php");	
        die();
    }
    else
    {
    	$codiceStazione=$_COOKIE["codiceStazione"];
    	// Controllo che il codice esiste
        $query="SELECT * FROM `stazioni` WHERE hash='$codiceStazione'";
        $risultato=$conn->query($query);
        if(mysqli_num_rows($risultato)=='0')
        {
        	$_SESSION['messaggio']="<div class='alert alert-warning'>Inserisci il codice di una stazione meteo</div>";
            header("location: /login.php");	
            die();
        }
    }     
  $sqlquery="SELECT CONCAT(data,' ', ora) AS DATAORA, HOUR(ora) AS ORA, AVG(temperatura) as TEMPERATURA, AVG(umidita) as UMIDITA, AVG(pressione) as PRESSIONE
            FROM dati
            WHERE CONCAT(data,' ', ora) >= DATE_SUB(NOW(),INTERVAL 13 HOUR) AND codiceStazione='$codiceStazione'
            group by HOUR(ora)
            ORDER BY data, ora  ASC";
  $risultato=$conn->query($sqlquery);
  $temperatura='[';
  $umidita='[';
  $pressione='[';  
  $contaOra = date('Y-m-d H:i:s', strtotime('-12 hour'));
  while($riga=$risultato->fetch_assoc())
  {  
  	//echo "RIGA DI ORA: ".date('H',strtotime($contaOra));

  	if(substr("00".$riga['ORA'],-2)==date('H',strtotime($contaOra)))
    {
      $temperatura.= round($riga['TEMPERATURA'],2) .", ";
      $umidita.=round($riga['UMIDITA'],2) .", ";
      $pressione.=round($riga['PRESSIONE']/100,2) .", ";
      $contaOra = date('Y-m-d H:i:s',strtotime('+1 hour',strtotime ($contaOra)));  
    }
    else
    {
    	while($contaOra<$riga['DATAORA'])
        {
          $temperatura.=", ";
          $umidita.=", ";
          $pressione.=", ";  
          $contaOra = date('Y-m-d H:i:s',strtotime('+1 hour',strtotime ($contaOra)));
        }
    
    }
     
  }
 
  $temperatura.="]";
  $umidita.="]";
  $pressione.="]";
  
  $sqlquery="SELECT AVG(temperatura) as MEDIA_TEMP, AVG(umidita) as MEDIA_UMI, AVG(pressione) as MEDIA_PRESS, MAX(temperatura) as MAX_TEMP, MIN(temperatura) as MIN_TEMP, 
  			MAX(umidita) as MAX_UMI, MIN(umidita) as MIN_UMI, MAX(pressione) as MAX_PRESS, MIN(pressione) as MIN_PRESS
            FROM dati
            WHERE CONCAT(data,' ', ora) >= DATE_SUB(NOW(),INTERVAL 12 HOUR) AND codiceStazione='$codiceStazione'
            group by data";
  $risultato=$conn->query($sqlquery);
  $maxTemp="N.D";
  $minTemp="N.D";
  $maxUmi="N.D";
  $minUmi="N.D";
  $maxPress="N.D";
  $minPress="N.D";
  $mediaTemp="N.D";
  $mediaUmi="N.D";
  $mediaPress="N.D";
  if($risultato->num_rows!=0)
  {
  	$totale=$risultato->fetch_assoc();
    $maxTemp=round($totale['MAX_TEMP'],2);
    $minTemp=round($totale['MIN_TEMP'],2);
    $maxUmi=round($totale['MAX_UMI'],2);
    $minUmi=round($totale['MIN_UMI'],2);
    $maxPress=round($totale['MAX_PRESS']/100,2);
    $minPress=round($totale['MIN_PRESS']/100,2);
    $mediaTemp=round($totale['MEDIA_TEMP'],2);
    $mediaUmi=round($totale['MEDIA_UMI'],2);
    $mediaPress=round($totale['MEDIA_PRESS']/100,2);
  }
  
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/png" href="img/logo.png"/>
    <!-- BOOTSTRAP 4 -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    
    <!-- CUSTOM CSS -->
    <link href="CSS/simple-sidebar.css" rel="stylesheet">
    <link rel="stylesheet" href="stile-1.css">
    <link rel="stylesheet" href="CSS/tipografia8.css">
    <link rel="stylesheet" href="CSS/statistiche.css">
    <link rel="stylesheet" href="CSS/weather-icons.css">
    <script src="SCRIPT/Chart.js"></script>
    <title>SWS | Statistiche</title>
  </head>
  <body>
  	<div class="d-flex" id="wrapper">

      <div class="border-right" id="sidebar-wrapper" style="background-color: #4e73df; color:white">
        <div class="sidebar-heading" style="padding:20px"><b>Smart Weather Station</b></div>
        <div class="list-group list-group-flush">
          <a href="home.php" class="list-group-item list-group-item-action linkNavbar">Tempo Reale</a>
          <a href="statistiche.php" class="list-group-item list-group-item-action linkAttivo">Statistiche</a>
          <a href="storico.php" class="list-group-item list-group-item-action linkNavbar" >Storico giornaliero</a>
          <a href="pagina.php" class="list-group-item list-group-item-action linkNavbar" >Storico mensile</a>
          <a href="StazioneIOT.pdf" class="list-group-item list-group-item-action linkNavbar" >Informazioni</a>
          <a href="http://t.me/SmartWeatherStationBot" class="list-group-item list-group-item-action linkNavbar">Bot Telegram</a>
          <a href="cambiaCodice.php" class="list-group-item list-group-item-action linkNavbar">Cambia Codice</a>
        </div>
      </div>
    

      <!-- Page Content -->
      <div id="page-content-wrapper">

        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom d-md-none">         
 			<b>Smart Weather Station</b>
		  <button class="navbar-toggler" type="button" style="margin-left:auto" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ml-auto mt-2 mt-lg-0">
              <li class="nav-item active">
                <a class="nav-link" href="home.php">Tempo reale</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="statistiche.php">Statistiche</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="storico.php">Storico giornaliero</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="pagina.php">Storico mensile</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="StazioneIOT.pdf">Informazioni</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="http://t.me/SmartWeatherStationBot">Bot Telegram</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="cambiaCodice.php">Cambia Codice</a>
              </li>         
            </ul>
          </div>
        </nav>
        <div class="container-fluid">
          <div style="top:60px;">
            <h1 class="titolo" style="margin-left: 10px;">Statistiche</h1>
            <hr>
            <h2 class="sottotitolo">TEMPERATURA</h2>
            <hr style="width:90%;">
            <div class="row" style="margin-left: 1%; margin-right:1%;">
                <div class="col-md-6 card distanza-basso"><canvas id="grafico_temperatura" class="chartjs" width="auto" height="auto"></canvas></div>
                <div class="col-md-6 distanza-basso">
                  <!--  TABELLA TEMPERATURA - (DISPOSITIVI MOBILI)-->
                  <div class="table-responsive hidden-lg-up">
                    <table class="table table-striped table-bordered" style="border: 1px solid black">
                      <thead>
                        <tr>
                          <th class="centra sottotitolo-card">-</th>
                          <th class="centra sottotitolo-card">Valore ( °C )</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td class="centra sottotitolo-card">MAX</td>
                          <td class="centra valore-tabella"><?php echo $maxTemp ?></td>
                        </tr>
                        <tr>
                          <td class="centra sottotitolo-card">MIN</td>
                          <td class="centra valore-tabella"><?php echo $minTemp ?></td>
                        </tr>
                        <tr>
                          <td class="centra sottotitolo-card">MEDIO</td>
                          <td class="centra valore-tabella"><?php echo $mediaTemp ?></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
            </div>
          	<br>
            <h2 class="sottotitolo">UMIDIT&Agrave;</h2>
            <hr style="width:90%;">
            <div class="row" style="margin-left: 1%; margin-right:1%;">
                <div class="col-md-6 card distanza-basso"><canvas id="grafico_umidita" class="chartjs" width="auto" height="auto"></canvas></div>
                <div class="col-md-6 distanza-basso">
                  <!--  TABELLA UMIDITA - (DISPOSITIVI MOBILI)-->
                  <div class="table-responsive hidden-lg-up">
                    <table class="table table-striped table-bordered" style="border: 1px solid black">
                      <thead>
                        <tr>
                          <th class="centra sottotitolo-card">-</th>
                          <th class="centra sottotitolo-card">Valore ( <i class="wi wi-humidity"></i> )</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td class="centra sottotitolo-card">MAX</td>
                          <td class="centra valore-tabella"><?php echo $maxUmi ?></td>
                        </tr>
                        <tr>
                          <td class="centra sottotitolo-card">MIN</td>
                          <td class="centra valore-tabella"><?php echo $minUmi ?></td>
                        </tr>
                        <tr>
                          <td class="centra sottotitolo-card">MEDIO</td>
                          <td class="centra valore-tabella"><?php echo $mediaUmi ?></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
            </div>
            <br>
            <h2 class="sottotitolo">PRESSIONE</h2>
            <hr style="width:90%;">
            <div class="row" style="margin-left: 1%; margin-right:1%;">
                <div class="col-md-6 card distanza-basso"><canvas id="grafico_pressione" class="chartjs" width="auto" height="auto"></canvas></div>
                <div class="col-md-6 distanza-basso">
                  <!--  TABELLA PRESSIONE - (DISPOSITIVI MOBILI)-->
                  <div class="table-responsive hidden-lg-up">
                    <table class="table table-striped table-bordered" style="border: 1px solid black">
                      <thead>
                        <tr>
                          <th class="centra sottotitolo-card">-</th>
                          <th class="centra sottotitolo-card">Valore ( <i class="wi wi-barometer"></i> )</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td class="centra sottotitolo-card">MAX</td>
                          <td class="centra valore-tabella"><?php echo $maxPress ?></td>
                        </tr>
                        <tr>
                          <td class="centra sottotitolo-card">MIN</td>
                          <td class="centra valore-tabella"><?php echo $minPress ?></td>
                        </tr>
                        <tr>
                          <td class="centra sottotitolo-card">MEDIO</td>
                          <td class="centra valore-tabella"><?php echo $mediaPress ?></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
            </div>
          </div>
        </div>
      </div>
      </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script>
      // GRAFICO DELLA TEMPERATURA
      var ctx = document.getElementById("grafico_temperatura").getContext('2d');
      var myChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: ["-12h", "-11h", "-10h", "-9h", "-8h", "-7h","-6h", "-5h", "-4h", "-3h", "-2h", "-1h","Ora"],
          datasets: [{
            label: 'Temperatura',
            data: <?php echo $temperatura?>,
            backgroundColor: [
            'rgba(240, 146, 0,0.2)'
            ],
            borderColor: [
            'rgba(240, 146, 0,1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            yAxes: [{
              ticks: {
                beginAtZero:true
              }
            }]
          }
        }
      });
      // GRAFICO DELLA PRESSIONE
      var ctx = document.getElementById("grafico_pressione").getContext('2d');
      var myChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: ["-12h", "-11h", "-10h", "-9h", "-8h", "-7h","-6h", "-5h", "-4h", "-3h", "-2h", "-1h","Ora"],
          datasets: [{
            label: 'Pressione',
            data: <?php echo $pressione?>,
            backgroundColor: [
            'rgba(155, 155, 155,0.2)'
            ],
            borderColor: [
            'rgba(155, 155, 155,1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          scales: {

            yAxes: [{
              ticks: {
                beginAtZero:true
              }
            }]
          }
        }
      });
      // GRAFICO DELLA UMIDITA
      var ctx = document.getElementById("grafico_umidita").getContext('2d');
      var myChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: ["-12h", "-11h", "-10h", "-9h", "-8h", "-7h","-6h", "-5h", "-4h", "-3h", "-2h", "-1h","Ora"],
          datasets: [{
            label: 'Umidità',
            data: <?php echo $umidita?>,
            backgroundColor: [
            'rgba(30, 150, 255,0.2)'
            ],
            borderColor: [
            'rgba(30, 150, 255,1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            yAxes: [{
              ticks: {
                beginAtZero:true
              }
            }]
          }
        }
      });
    </script>
  </body>
  <?php   $conn->close(); ?>
</html>
