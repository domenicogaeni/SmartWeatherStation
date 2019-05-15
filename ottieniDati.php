<?php
  require 'conn.php';
  $data_oggi = date("Y-m-d");
  if(!isset($_COOKIE["codiceStazione"]))
  {
  	$_SESSION['messaggio']="<div class='alert alert-info'>Inserisci il codice della stazione meteo</div>";
    header("location: /login.php");	
    die();
  }  
  $codiceStazione=$_COOKIE["codiceStazione"];
  // Prelevo i dati di oggi
  $sqlquery="SELECT * FROM dati where `data`='$data_oggi' AND `codiceStazione`='$codiceStazione' order by id DESC";
  $result=$conn->query($sqlquery);
  $number = mysqli_num_rows($result);
  $i=0;
  $temperatura_adesso=-100;
  while ($value = $result->fetch_assoc())
  {
    if($i==0)
    {
      $temperatura_adesso=$value['temperatura'];
      $pressione_adesso=$value['pressione']/100;
      $umidita_adesso=$value['umidita'];
    }
    $ora[$i]=$value['ora'];
    $temperatura[$i]=$value['temperatura'];
    $pressione[$i]=$value['pressione']/100;
    $umidita[$i]=$value['umidita'];
    $i++;
  }
  // Se non sono presenti dati
  if($number=='0')
  {
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
    $stato="<span class='badge badge-danger' style='font-size: 20px;'>OFFLINE</span>";
  }
  else
  {
    $stato="<span class='badge badge-success' style='font-size: 20px;'>ONLINE</span>";
    // Scelgo il colore in base alla temperatura
    if ($temperatura_adesso<=15)
      $colore="style='color:#87cefa'";
    else if ($temperatura_adesso<=22)
        $colore="style='color: black'";
    else
        $colore="style='color: #f09200'";

    //
    // CALCOLO LA PREVISIONE ATTUALE
    //
    $contenuto=file_get_contents("http://api.openweathermap.org/data/2.5/weather?q=Bergamo,Italy&appid=7127b412920c2e58bc4c6b2130b39360");
    $json = json_decode($contenuto, true);
    $tempo=$json['weather'][0]['main'];
    if (strpos(strtolower($tempo), 'clear') !== false)
    {
        $icon="<i class='wi wi-day-sunny icona-previ'></i>";
        $previsione="SOLE";
    }
    else if (strpos(strtolower($tempo), 'cloud') !== false)
    {
        $icon="<i class='wi wi-day-cloudy icona-previ'></i>";
        $previsione="NUVOLE";
    }
    else if(strpos(strtolower($tempo), 'snow') !== false)
    {
      $icon="<i class='wi wi-day-snow icona-previ'></i>";
      $previsione="NEVE";
    }
    else if (strpos(strtolower($tempo), 'rain') !== false)
    {
      $icon="<i class='wi wi-day-rain icona-previ'></i>";
      $previsione="PIOGGIA";
    }
    else if (strpos(strtolower($tempo), 'storm') !== false)
    {
      $icon="<i class='wi-day-thunderstorm icona-previ'></i>";
      $previsione="TEMPORALE";
    }
    
    
  
    // Imposto dei valori che non potranno essere mai raggiunti per trovare il MAX e il MIN
    $temp_max=$temperatura[0];
    $temp_min=$temperatura[0];
    $umi_max=$umidita[0];
    $umi_min=$umidita[0];
    $press_max=$pressione[0];
    $press_min=$pressione[0];
    // Trovo la TEMPERATURA - UMIDITA - PRESSIONE massima e minima
    // Calcolo anche la media delle PRESSIONI
    $media_pres=$pressione[0];
    for ($k=1; $k <$i ; $k++)
    {
      if($temperatura[$k]>$temp_max)
        $temp_max=$temperatura[$k];
      else if($temperatura[$k]<$temp_min)
        $temp_min=$temperatura[$k];

      if($umidita[$k]>$umi_max)
        $umi_max=$umidita[$k];
      else if($umidita[$k]<$umi_min)
        $umi_min=$umidita[$k];

      if($pressione[$k]>$press_max)
        $press_max=$pressione[$k];
      else if($pressione[$k]<$press_min)
        $press_min=$pressione[$k];

      $media_pres+=$pressione[$k];
    }
    $media_pres/=$i;

    //
    // Scelgo la previsione DELLA GIORNATA (calcolata come media tra le previsioni)
    //
    
    // Arrotondo la pressione adesso in modo da non averlo con la virgola nella HOME
    $pressione_adesso= round($pressione_adesso);

  }
    if(isset($_GET['datiAdesso'])&&$_GET['datiAdesso'])
    {
      echo "<div class='col-12 col-sm-6 col-md-3 distanza-basso' $colore >
      <div class='dati'>$temperatura_adesso <i class='wi wi-celsius' style='font-size: 30px;'></i></div>
      <h5 style='font-weight:bold'>Temperatura (°C)</h5>
      </div>
      <div class='col-12 col-sm-6 col-md-3 distanza-basso' style='color: rgb(30, 150, 255)'>
      <div class='dati'>$umidita_adesso <i class='wi wi-humidity' style='font-size: 30px;'></i></div>
      <h5 style='font-weight:bold'>Umidit&agrave; (%)</h5>
      </div>
      <div class='col-12 col-sm-6 col-md-3 distanza-basso' style='color: #555;'>
      <div class='dati'>$pressione_adesso <i class='wi wi-barometer' style='font-size: 30px;'></i></div>
      <h5 style='font-weight:bold'>Pressione (mb)</h5>
      </div>
      <div class='col-12 col-sm-6 col-md-3 distanza-basso' $colore>
      <div style='font-size:55px;'>$icon</div>
      <h5 style='font-weight:bold'> $previsione </h5>
      </div>";
    }
    if(isset($_GET['temperatura'])&&$_GET['temperatura'])
    {
              
      echo "<div class='card bg-light' style='height: 200px;'>
      			<div class='card-header' style='padding:10px'>
                	<h2 class='titolo-card'><b>Temperatura</b></h2>
                </div>
                <div class='card-body'>                
                    <div class='row'>
                      <div class='col-6'>
                        <h3 class='sottotitolo-card'>MAX</h3>
                        <h3 class='valore-card'>$temp_max <i class='wi wi-celsius'></i></h3>
                      </div>
                      <div class='col-6'>
                        <h3 class='sottotitolo-card'>MIN</h3>
                        <h3 class='valore-card'>$temp_min <i class='wi wi-celsius'></i></h3>
                      </div>
                    </div>
                  </div>        
      		</div>";
    }
    if(isset($_GET['umidita'])&&$_GET['umidita'])
    {
    echo"<div class='card bg-light' style='height: 200px;'>
                  <div class='card-header' style='padding:10px'>
                    <h2 class='titolo-card'><b>Umidit&agrave;</b></h2>
                  </div>
                  <div class='card-body' style='background-color:white'>                
                    <div class='row'>
                      <div class='col-6'>
                        <h3 class='sottotitolo-card'>MAX</h3>
                        <h3 class='valore-card'>$umi_max <i class='wi wi-humidity'></i></h3>
                      </div>
                      <div class='col-6'>
                        <h3 class='sottotitolo-card'>MIN</h3>
                        <h3 class='valore-card'>$umi_min <i class='wi wi-humidity'></i></h3>
                      </div>
                    </div>
                  </div>
                </div>";
    }
    
    if(isset($_GET['pressione'])&&$_GET['pressione'])
    {
    echo "<div class='card bg-light' style='height: 200px;'>
                  <div class='card-header' style='padding:10px'>
                    <h6 class='titolo-card'><b>Pressione</b></h6>
                  </div>
                  <div class='card-body' style='background-color:white'>                
                    <div class='row'>
                      <div class='col-6'>
                        <h3 class='sottotitolo-card'>MAX</h3>
                        <h3 class='valore-card'>$press_max <i class='wi wi-barometer'></i></h3>
                      </div>
                      <div class='col-6'>
                        <h3 class='sottotitolo-card'>MIN</h3>
                        <h3 class='valore-card'>$press_min <i class='wi wi-barometer'></i></h3>
                      </div>
                    </div>
                  </div>
                </div>";
    }
    if(isset($_GET['previsione'])&&$_GET['previsione'])
    {
     echo "<div class='card bg-light' style='height: 200px;'>
                  <div class='card-header' style='padding:10px'>
                    <h2 class='titolo-card'><b>Previsione</b></h2>
                  </div>
                   <div class='card-body'>                
                    <div class='row'>
                      <div class='col-6'>
                         $icon
                      </div>
                      <div class='col-6'>
                        <h3 class='sottotitolo-card'>$previsione</h3>
                      </div>
                    </div>
                   </div>
                </div>";     
    }
    $conn->close();
?>
