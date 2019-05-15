<?php
	session_start();
    require 'conn.php';
    if (!isset($_COOKIE["codiceStazione"]))
    {
        $_SESSION['messaggio']="<div class='alert alert-info'>Inserisci il codice della stazione meteo, per esempio A000</div>";
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
        else
        {
        	while($row=mysqli_fetch_assoc($risultato))
            {
            	$codiceTradotto=$row['codice'];
            }
        }
    }
	$booleanaOffline="true";
    $data_oggi = date("Y-m-d");
    //
    // Prelevo L'ultimo dato registato oggi
    //
  	$sqlquery="SELECT temperatura,pressione,umidita,data,ora FROM dati where `data`='$data_oggi' AND `codiceStazione`='$codiceStazione' order by id DESC LIMIT 1";
  	$result=$conn->query($sqlquery);
  	$number = mysqli_num_rows($result);    
    // Se non sono presenti dati, sistemo i valori della stazione e metto l'etichetta OFFLINE
    if($number!='0')
    {
      $value=$result->fetch_assoc();
      $temperatura_adesso=$value['temperatura'];
      $pressione_adesso=round($value['pressione']/100);
      $umidita_adesso=$value['umidita'];
      //PRENDO L'ULTIMA DATA
      $data_adesso=$value['data']." ".$value['ora'];
      $format = "Y-m-d H:i:s";
      $dateobj = DateTime::createFromFormat($format, $data_adesso);
      $minuti=date("i",time()-$dateobj->getTimestamp());
      $stato="<h1 class='titolo badge badge-success' style='margin-left: 10px; font-size: 23px;'>Tempo Reale - ONLINE</h1>";
      // Scelgo il colore in base alla temperatura
      if ($temperatura_adesso<=15)
        $colore="style='color:#87cefa'";
      else if ($temperatura_adesso<=22)
          $colore="style='color: black'";
      else
          $colore="style='color: #f09200'";
    } 
    if($number=='0'|| $minuti>=2)
    {
      $booleanaOffline="false";
      $temperatura_adesso="<i class='wi wi-na'></i>";
      $pressione_adesso="<i class='wi wi-na'></i>";
      $umidita_adesso="<i class='wi wi-na'></i>";
      $temp_max="<i class='wi wi-na'></i>";
      $temp_min="<i class='wi wi-na'></i>";
      $umi_max="<i class='wi wi-na'></i>";
      $umi_min="<i class='wi wi-na'></i>";
      $press_max="<i class='wi wi-na'></i>";
      $press_min="<i class='wi wi-na'></i>";
      $icon="<i class='wi wi-na'></i>";
      $icon_giorno="<i class='wi wi-na icona-previ'></i>";
      $previsione="N.D.";
      $previsione_giorno="<i class='wi wi-na icona-previ'></i>";
      $colore="style='color: black'";
      $stato="<h1 class='titolo badge badge-danger' style='margin-left: 10px; font-size: 23px;'>Tempo Reale - OFFLINE</h1>";
    }
    
    //
    // Prelevo dal DB i valori MASSIMI e minimi di ogni parametro
    //
    $sqlquery="SELECT MAX(temperatura) AS TEMPMAX, MIN(temperatura) AS TEMPMIN, MAX(umidita) AS UMIMAX, MIN(umidita) AS UMIMIN, MAX(pressione) AS PRESSMAX, MIN(pressione) AS PRESSMIN
                FROM dati
                WHERE data='$data_oggi' AND codiceStazione='$codiceStazione'
                ORDER BY id DESC";
  	$result=$conn->query($sqlquery);
  	$number = mysqli_num_rows($result);
        
    if($number!=0)
    {
      $value=$result->fetch_assoc();      
      $temp_max=round($value['TEMPMAX'],2);
      $temp_min=round($value['TEMPMIN'],2);
      $umi_max=round($value['UMIMAX'],2);
      $umi_min=round($value['UMIMIN'],2);
      $press_max=round($value['PRESSMAX']/100);
      $press_min=round($value['PRESSMIN']/100);
      //
      // Scelgo la previsione DELLA GIORNATA (calcolata come media tra le previsioni)
      //
      $contenuto=file_get_contents("http://api.openweathermap.org/data/2.5/weather?q=Bergamo,Italy&appid=7127b412920c2e58bc4c6b2130b39360");
      $json = json_decode($contenuto, true);
      $tempo=$json['weather'][0]['main'];

      if (strpos(strtolower($tempo), 'clear') !== false)
      {
          $icon_giorno="<i class='wi wi-day-sunny icona-previ'></i>";
          $previsione_giorno="SOLE";
      }
      else if (strpos(strtolower($tempo), 'cloud') !== false)
      {
          $icon_giorno="<i class='wi wi-day-cloudy icona-previ'></i>";
          $previsione_giorno="NUVOLE";
      }
      else if(strpos(strtolower($tempo), 'snow') !== false)
      {
        $icon_giorno="<i class='wi wi-day-snow icona-previ'></i>";
        $previsione_giorno="NEVE";
      }
      else if (strpos(strtolower($tempo), 'rain') !== false)
      {
        $icon_giorno="<i class='wi wi-day-rain icona-previ'></i>";
        $previsione_giorno="PIOGGIA";
      }
      else if (strpos(strtolower($tempo), 'storm') !== false)
      {
        $icon_giorno="<i class='wi-day-thunderstorm icona-previ'></i>";
        $previsione_giorno="TEMPORALE";
      }    
   }

?>
<!DOCTYPE html>
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

    <!-- CUSTOM CSS -->
    <link href="CSS/simple-sidebar.css" rel="stylesheet">
    <link rel="stylesheet" href="stile-1.css">
    <link rel="stylesheet" href="CSS/tipografia8.css">
    <link rel="stylesheet" href="CSS/weather-icons.css">
    <title>SWS | Tempo reale</title>
    <script>
        function startTime()
        {
        	if(<?php echo $booleanaOffline ?>)
          		setInterval(aggiorna, 50000);
        }
        function aggiorna()
        {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function()
            {
              if (this.readyState == 4 && this.status == 200)
              {
                document.getElementById('datiAttuali').innerHTML=this.responseText;
              }
            };
            xhttp.open("GET", "ottieniDati.php?datiAdesso=true", true);
            xhttp.send();
            // Temperatura
            var thttp = new XMLHttpRequest();
            thttp.onreadystatechange = function()
            {
              if (this.readyState == 4 && this.status == 200)
              {
                document.getElementById('temperatura').innerHTML=this.responseText;
              }
            };
            thttp.open("GET", "ottieniDati.php?temperatura=true", true);
            thttp.send();
            // Umidita
            var uhttp = new XMLHttpRequest();
            uhttp.onreadystatechange = function()
            {
              if (this.readyState == 4 && this.status == 200)
              {
                document.getElementById('umidita').innerHTML=this.responseText;
              }
            };
            uhttp.open("GET", "ottieniDati.php?umidita=true", true);
            uhttp.send();
            // Pressione
            var phttp = new XMLHttpRequest();
            phttp.onreadystatechange = function()
            {
              if (this.readyState == 4 && this.status == 200)
              {
                document.getElementById('pressione').innerHTML=this.responseText;
              }
            };
            phttp.open("GET", "ottieniDati.php?pressione=true", true);
            phttp.send();
            // Previsione
            var pphttp = new XMLHttpRequest();
            pphttp.onreadystatechange = function()
            {
              if (this.readyState == 4 && this.status == 200)
              {
                document.getElementById('previsione').innerHTML=this.responseText;
              }
            };
            pphttp.open("GET", "ottieniDati.php?previsione=true", true);
            pphttp.send();
        }
    </script>
  </head>
  <body onload="startTime()">
    
    <div class="d-flex" id="wrapper">

      <div class="border-right" id="sidebar-wrapper" style="background-color: #4e73df; color:white">
        <div class="sidebar-heading" style="padding:20px"><b>Smart Weather Station</b></div>
        <div class="list-group list-group-flush">
          <a href="home.php" class="list-group-item list-group-item-action linkAttivo">Tempo Reale</a>
          <a href="statistiche.php" class="list-group-item list-group-item-action linkNavbar">Statistiche</a>
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
            <?php echo $stato ?>
            <hr>
            <div class="card">
              <div class="row" style="margin-top: 30px; margin-bottom: 30px;" id="datiAttuali">
                <div class="col-12 col-sm-6 col-md-3 distanza-basso" <?php echo $colore?> >
                  <div class="dati"> <?php echo $temperatura_adesso?> <i class="wi wi-celsius" style="font-size: 30px;"></i></div>
                  <h5><b>Temperatura (°C)</b></h5>
                </div>
                <div class="col-12 col-sm-6 col-md-3 distanza-basso" style="color: rgb(30, 150, 255)">
                  <div class="dati"> <?php echo $umidita_adesso?> <i class="wi wi-humidity" style="font-size: 30px;"></i></div>
                  <h5><b>Umidit&agrave; (%)</b></h5>
                </div>
                <div class="col-12 col-sm-6 col-md-3 distanza-basso" style="color: #555;">
                  <div class="dati"><?php echo $pressione_adesso ?> <i class="wi wi-barometer" style="font-size: 30px;"></i></div>
                  <h5><b>Pressione (mb)</b></h5>
                </div>
                <div class="col-12 col-sm-6 col-md-3 distanza-basso" <?php echo $colore?>>
                  <div style="font-size:55px;"><?php echo $icon_giorno ?></div>
                  <h5><b><?php echo $previsione_giorno ?></b></h5>
                </div>
              </div>
            </div>
            <br>
            <h2 class="titolo" style="margin-left: 10px;">Rilevazioni giornaliere</h2>
            <hr>
            <div class="row distanza-basso">
              <div class="col-md-6 distanza-basso" id="temperatura">
                <div class="card bg-light" style="height: 200px;">
                  <div class="card-header" style="padding:10px">
                    <h2 class="titolo-card"><b>Temperatura</b></h2>
                  </div>
                  <div class="card-body">                
                    <div class="row">
                      <div class="col-6">
                        <h3 class="sottotitolo-card">MAX</h3>
                        <h3 class="valore-card"><?php echo $temp_max ?> <i class="wi wi-celsius"></i></h3>
                      </div>
                      <div class="col-6">
                        <h3 class="sottotitolo-card">MIN</h3>
                        <h3 class="valore-card"><?php echo $temp_min ?> <i class="wi wi-celsius"></i></h3>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6 distanza-basso" style="color: rgb(30, 150, 255)" id="umidita">
                <div class="card bg-light" style="height: 200px;">
                  <div class="card-header" style="padding:10px">
                    <h2 class="titolo-card"><b>Umidit&agrave;</b></h2>
                  </div>
                  <div class="card-body" style="background-color:white">                
                    <div class="row">
                      <div class="col-6">
                        <h3 class="sottotitolo-card">MAX</h3>
                        <h3 class="valore-card"><?php echo $umi_max ?> <i class="wi wi-humidity"></i></h3>
                      </div>
                      <div class="col-6">
                        <h3 class="sottotitolo-card">MIN</h3>
                        <h3 class="valore-card"><?php echo $umi_min ?> <i class="wi wi-humidity"></i></h3>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="row distanza-basso">
              <div class="col-md-6 distanza-basso" id="pressione">
                <div class="card bg-light" style="height: 200px;">
                  <div class="card-header" style="padding:10px">
                    <h6 class="titolo-card"><b>Pressione</b></h6>
                  </div>
                  <div class="card-body" style="background-color:white">                
                    <div class="row">
                      <div class="col-6">
                        <h3 class="sottotitolo-card">MAX</h3>
                        <h3 class="valore-card"><?php echo $press_max ?> <i class="wi wi-barometer"></i></h3>
                      </div>
                      <div class="col-6">
                        <h3 class="sottotitolo-card">MIN</h3>
                        <h3 class="valore-card"><?php echo $press_min ?> <i class="wi wi-barometer"></i></h3>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6 distanza-basso" id="previsione">
                <div class="card bg-light" style="height: 200px;">
                  <div class="card-header" style="padding:10px">
                    <h2 class="titolo-card"><b>Previsione</b></h2>
                  </div>
                   <div class="card-body">                
                    <div class="row">
                      <div class="col-6">
                        <?php echo $icon_giorno ?>
                      </div>
                      <div class="col-6">
                        <h3 class="sottotitolo-card"><?php echo $previsione_giorno ?></h3>
                      </div>
                    </div>
                   </div>
                </div>
              </div>
            </div>
          </div>
      </div>
    </div>
  </div>

  <script>
    $("#menu-toggle").click(function(e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });
  </script>
  <?php   $conn->close(); ?>
  </body>
</html>
