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

  // Prelevo, se c'è, la data da cercare in GET - se non c'è metto la data di oggi
  $data_cerca=isset($_GET['data']);
  if($data_cerca){
    $data_cerca=$_GET['data'];
  }else{
    $data_cerca = date("Y-m-d");
  }

  $query="SELECT HOUR(ora) as ORA, AVG(temperatura) AS TEMPERATURA, AVG(umidita) AS UMIDITA, AVG(pressione) AS PRESSIONE
  		  FROM dati
          WHERE data='$data_cerca' and codiceStazione='$codiceStazione'
          GROUP BY HOUR(ora)
          ORDER BY ORA";
  $risultato=$conn->query($query);
  $temperatura="[";
  $umidita="[";
  $pressione="[";
  $contaOra=0;
  
  while($riga=$risultato->fetch_assoc())
  {
  	while($contaOra<$riga['ORA'])
    {
      $temperatura.=", ";
      $umidita.=", ";
      $pressione.=", ";  
      $contaOra++;
    }
    if($riga['ORA']==$contaOra)
    {
      $temperatura.=round($riga['TEMPERATURA'],2).", ";
      $umidita.=round($riga['UMIDITA'],2).", ";
      $pressione.=round($riga['PRESSIONE'],2).", ";
    }
    $contaOra++;    
  }
 
  $temperatura.="]";
  $umidita.="]";
  $pressione.="]";
 
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
    <title>SWS | Storico</title>
  </head>
  <body>
    <div class="d-flex" id="wrapper">

      <div class="border-right" id="sidebar-wrapper" style="background-color: #4e73df; color:white">
        <div class="sidebar-heading" style="padding:20px"><b>Smart Weather Station</b></div>
        <div class="list-group list-group-flush">
          <a href="home.php" class="list-group-item list-group-item-action linkNavbar">Tempo Reale</a>
          <a href="statistiche.php" class="list-group-item list-group-item-action linkNavbar">Statistiche</a>
          <a href="storico.php" class="list-group-item list-group-item-action linkAttivo" >Storico giornaliero</a>
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
              <h1 class="titolo" style="margin-left: 10px;">Storico:</h1>
              <hr>
              <form action="storico.php" method="get" class="form-inline" style="width:100%">
                <label class="sr-only" for="inlineFormInput">Data</label>
                  <input type="date" name="data" class="form-control mb-2 mr-sm-2 mb-sm-0" id="inlineFormInputGroup" value="<?php echo $data_cerca?>">
                <div class="form-group">
                 <label class="sr-only" for="inlineFormInput">Scelta visualizzazione</label>
                 <select class="form-control mb-2 mr-sm-2 mb-sm-0" id="inlineFormInputGroup" name="tipo">
                   <option value="grafici" <?php if($value_form=="Grafici") echo "selected"?>>Grafici</option>
                   <option value="tabella" <?php if($value_form=="Tabella") echo "selected"?>>Tabella</option>
                 </select>
                 <button type="submit" class="btn btn-primary">Cerca</button>
               </div>
              </form>
              <br>
              <h1 class="titolo-card" style="text-align:left;">Riepilogo del <?php echo $data_cerca?>:</h1>
              <hr>
              <h2 class="sottotitolo">TEMPERATURA</h2>
              <hr style="width:90%; display:<?php echo $display_grafici?>">
              <div class="col-12 card distanza-basso" style="display:<?php echo $display_grafici?>"><canvas id="grafico_temperatura" class="chartjs" width="auto" height="auto"></canvas></div>
              <br>
              <h2 class="sottotitolo">UMIDIT&Agrave;</h2>
              <hr style="width:90%; display:<?php echo $display_grafici?>">
              <div class="col-12 card distanza-basso" style="display:<?php echo $display_grafici?>"><canvas id="grafico_umidita" class="chartjs" width="auto" height="auto"></canvas></div>
              <br>
              <h2 class="sottotitolo">PRESSIONE</h2>
              <hr style="width:90%; display:<?php echo $display_grafici?>">
              <div class="col-12 card distanza-basso" style="display:<?php echo $display_grafici?>"><canvas id="grafico_pressione" class="chartjs" width="auto" height="auto"></canvas></div>
              <br><br>
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
                      labels: ["00:00h", "01:00h", "02:00h", "03:00h", "04:00h", "05:00h","06:00h", "07:00h", "08:00h", "09:00h", "10:00h", "11:00h","12:00h","13:00h","14:00h","15:00h","16:00h","17:00h","18:00h","19:00h","20:00h","21:00h","22:00h","23:00h"],
                      datasets: [
                        {
                            label: 'Temperatura',
                            data: <?php echo $temperatura?>,
                            backgroundColor: [
                                'rgba(255, 177, 68,0.2)'
                            ],
                            borderColor: [
                                'rgba(255, 177, 68,1)'
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
                      labels: ["00:00h", "01:00h", "02:00h", "03:00h", "04:00h", "05:00h","06:00h", "07:00h", "08:00h", "09:00h", "10:00h", "11:00h","12:00h","13:00h","14:00h","15:00h","16:00h","17:00h","18:00h","19:00h","20:00h","21:00h","22:00h","23:00h"],
                      datasets: [
                        {
                            label: 'Pressione',
                            data: <?php echo $pressione?>,
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
                      labels: ["00:00h", "01:00h", "02:00h", "03:00h", "04:00h", "05:00h","06:00h", "07:00h", "08:00h", "09:00h", "10:00h", "11:00h","12:00h","13:00h","14:00h","15:00h","16:00h","17:00h","18:00h","19:00h","20:00h","21:00h","22:00h","23:00h"],
                      datasets:
                      [
                        {
                            label: 'Umidità',
                            data: <?php echo $umidita?>,
                            backgroundColor: [
                                'rgba(30, 150, 255,0.2)'
                            ],
                            borderColor: [
                                'rgba(30, 150, 255,1)'
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
    <?php $conn->close(); ?>
</html>

