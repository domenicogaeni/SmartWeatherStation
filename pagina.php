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
  $meseEsame=date('n');
  
  if(isset($_GET['mese']))
  {
    $meseEsame=$_GET['mese'];    
  }
  $query="SELECT DAY(data) as GIORNO, MONTH(data) as MESE, YEAR(data) as ANNO, AVG(temperatura) AS TEMPERATURA, AVG(umidita) AS UMIDITA, AVG(pressione) AS PRESSIONE, MAX(temperatura) AS TEMPMAX, MAX(umidita) AS UMIMAX, MAX(pressione) AS PRESSMAX, MIN(temperatura) AS TEMPMIN, MIN(umidita) AS UMIMIN, MIN(pressione) AS PRESSMIN
      FROM dati
      WHERE codiceStazione='$codiceStazione' AND YEAR(data)=YEAR(CURDATE())
      GROUP BY DAY(data), MONTH(data), YEAR(data)
      HAVING MESE = '$meseEsame'
      ORDER BY DAY(data)";
  $risultato=$conn->query($query);
  $giorniMaxMese=cal_days_in_month(CAL_GREGORIAN, $meseEsame, date('Y'));
  $datiTemperatura="[";
  $datiUmidita="[";
  $datiPressione="[";
  $MaxTemperatura="[";
  $MaxUmidita="[";
  $MaxPressione="[";
  $minTemperatura="[";
  $minUmidita="[";
  $minPressione="[";

  $etichetteMese="[";
  for ($i=1; $i <= $giorniMaxMese; $i++) { 
    $etichetteMese.=$i.", ";
  }
  $etichetteMese.="]";
  $contaGiorno=1;  
  while($riga=$risultato->fetch_assoc())
  {
    while($contaGiorno<$riga['GIORNO'])
    {
      $datiTemperatura.=", ";
      $datiUmidita.=", ";
      $datiPressione.=", ";
      $MaxTemperatura.=", ";
      $MaxUmidita.=", ";
      $MaxPressione.=", ";
      $minTemperatura.=", ";
      $minUmidita.=", ";
      $minPressione.=", ";  
      $contaGiorno++;
    }
    if($riga['GIORNO']==$contaGiorno)
    {
      $datiTemperatura.=round($riga['TEMPERATURA'],2).", ";
      $datiUmidita.=round($riga['UMIDITA'],2).", ";
      $datiPressione.=round($riga['PRESSIONE'],2).", ";
      $MaxTemperatura.=round($riga['TEMPMAX'],2).", ";
      $MaxUmidita.=round($riga['UMIMAX'],2).", ";
      $MaxPressione.=round($riga['PRESSMAX'],2).", ";
      $minTemperatura.=round($riga['TEMPMIN'],2).", ";
      $minUmidita.=round($riga['UMIMIN'],2).", ";
      $minPressione.=round($riga['PRESSMIN'],2).", ";
    }
    $contaGiorno++;    
  }
  $datiTemperatura.="]";
  $datiUmidita.="]";
  $datiPressione.="]";
  $MaxTemperatura.="]";
  $MaxUmidita.="]";
  $MaxPressione.="]";
  $minTemperatura.="]";
  $minUmidita.="]";
  $minPressione.="]";

  $arrayMese=array("Gennaio","Febbraio","Marzo","Aprile","Maggio","Giugno","Luglio","Agosto","Settembre","Ottobre","Novembre","Dicembre");
?>
<DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/png" href="img/logo.png"/>
    <!-- BOOTSTRAP -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    
    <link href="CSS/simple-sidebar.css" rel="stylesheet">
    <link rel="stylesheet" href="stile-1.css">
    <link rel="stylesheet" href="CSS/tipografia8.css">
    <link rel="stylesheet" href="CSS/weather-icons.css">
    <script src="SCRIPT/Chart.js"></script>
    
    <title>SWS | Storico Mensile</title>
    
    <script>
    	function manda()
        {
        	document.getElementById('mioForm').submit();
        }
    </script>
  </head>
  <body>
    <div class="d-flex" id="wrapper">

      <div class="border-right" id="sidebar-wrapper" style="background-color: #4e73df; color:white">
        <div class="sidebar-heading" style="padding:20px"><b>Smart Weather Station</b></div>
        <div class="list-group list-group-flush">
          <a href="home.php" class="list-group-item list-group-item-action linkNavbar">Tempo Reale</a>
          <a href="statistiche.php" class="list-group-item list-group-item-action linkNavbar">Statistiche</a>
          <a href="storico.php" class="list-group-item list-group-item-action linkNavbar" >Storico giornaliero</a>
          <a href="pagina.php" class="list-group-item list-group-item-action linkAttivo" >Storico mensile</a>
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
              <h1 class="titolo" style="margin-left: 10px;">Storico mensile</h1>
              <div class="container">
                <br>
                <form id="mioForm">
                  <div class="form-group">
                    <select class="form-control" name="mese" onchange="manda()">
                      <?php
                          $i=0;
                          while($i<12)
                          {
                              if($meseEsame-1==$i)
                                  echo "<option selected value='". ($i+1) ."'>".$arrayMese[$i]."</option>";                        	
                              else
                                  echo "<option value='". ($i+1) ."'>".$arrayMese[$i]."</option>";
                              $i++;
                          }

                      ?>
                    </select>
                  </div>
                </form>
              </div>
              <h2 class="sottotitolo">TEMPERATURA</h2>
              <div class="col-12 card distanza-basso"><canvas id="grafico_temperatura" class="chartjs" width="auto" height="auto"></canvas></div>
              <br>
              <h2 class="sottotitolo">UMIDIT&Agrave;</h2>
              <div class="col-12 card distanza-basso"><canvas id="grafico_umidita" class="chartjs" width="auto" height="auto"></canvas></div>
              <br>
              <h2 class="sottotitolo">PRESSIONE</h2>
              <div class="col-12 card distanza-basso"><canvas id="grafico_pressione" class="chartjs" width="auto" height="auto"></canvas></div>        
              <br><br>
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
                      labels: <?=$etichetteMese?>,
                      datasets: [
                        {
                            label: 'Temperatura',
                            data: <?php echo $datiTemperatura?>,
                            backgroundColor: [
                                'rgba(255, 177, 68,0.2)'
                            ],
                            borderColor: [
                                'rgba(255, 177, 68,1)'
                            ],
                            borderWidth: 1
                        },
                       {
                            label: 'Temperatura Massima',
                            data: <?php echo $MaxTemperatura?>,
                            backgroundColor: [
                                'rgba(255, 170, 53,0.2)'
                            ],
                            borderColor: [
                                'rgba(255, 170, 53,1)'
                            ],
                            borderWidth: 1
                        },
                        {
                            label: 'Temperatura minima',
                            data: <?php echo $minTemperatura?>,
                            backgroundColor: [
                                'rgba(240, 146, 0,0.2)'
                            ],
                            borderColor: [
                                'rgba(240, 146, 0,1)'
                            ],
                            borderWidth: 1
                        } 
                      ]
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
                      labels: <?=$etichetteMese?>,
                      datasets: [
                        {
                            label: 'Pressione',
                            data: <?php echo $datiPressione?>,
                            backgroundColor: [
                                'rgba(155, 155, 155,0.2)'
                            ],
                            borderColor: [
                                'rgba(155, 155, 155,1)'
                            ],
                            borderWidth: 1
                        },
                        {
                            label: 'Pressione minima',
                            data: <?php echo $minPressione?>,
                            backgroundColor: [
                                'rgba(155, 155, 155,0.2)'
                            ],
                            borderColor: [
                                'rgba(155, 155, 155,1)'
                            ],
                            borderWidth: 1
                        },
                        {
                            label: 'Pressione Massima',
                            data: <?php echo $MaxPressione?>,
                            backgroundColor: [
                                'rgba(155, 155, 155,0.2)'
                            ],
                            borderColor: [
                                'rgba(155, 155, 155,1)'
                            ],
                            borderWidth: 1
                        }
                      ]
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
                      labels: <?=$etichetteMese?>,
                      datasets:
                      [
                        {
                            label: 'Umidità',
                            data: <?php echo $datiUmidita?>,
                            backgroundColor: [
                                'rgba(30, 150, 255,0.2)'
                            ],
                            borderColor: [
                                'rgba(30, 150, 255,1)'
                            ],
                            borderWidth: 1
                        },
                        {
                            label: 'Umidità Massima',
                            data: <?php echo $MaxUmidita?>,
                            backgroundColor: [
                                'rgba(68, 208, 255,0.2)'
                            ],
                            borderColor: [
                                'rgba(68, 208, 255,1)'
                            ],
                            borderWidth: 1
                        },
                        {
                            label: 'Umidità Minima',
                            data: <?php echo $minUmidita?>,
                            backgroundColor: [
                                'rgba(41, 30, 255,0.2)'
                            ],
                            borderColor: [
                                'rgba(41, 30, 255,1)'
                            ],
                            borderWidth: 1
                        }  
                     ]
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