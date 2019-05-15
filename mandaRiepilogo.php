 <?php
  $botToken="615463090:AAH_IaKY8gBSTNIf_4-V9X7y82QNoDIGCRU";
  $website="https://api.telegram.org/bot".$botToken;
  // Connessione al db
  require 'conn.php';

	$query="SELECT * FROM utenti";
    $risu=$conn->query($query);
    while($riga = $risu->fetch_assoc())
    {
       	$chatId=$riga['chatId'];
       	$query="SELECT * FROM utenti WHERE chatId='$chatId'";
        $result=$conn->query($query);
        $riga=mysqli_fetch_assoc($result);
        $codiceStazione=$riga['codiceStazione'];  
        if(mysqli_num_rows($result)==0)
          $codiceStazione="a";

        $data_oggi = date("Y-m-d");
        // Prelevo i dati di oggi
        $sqlquery="SELECT * FROM dati where `data`='$data_oggi' AND `codiceStazione`='$codiceStazione' order by id DESC";
        $result=$conn->query($sqlquery);
        $number = mysqli_num_rows($result);
        $i=0;
        $temperatura_corrente="N.D.";
        $pressione_corrente="N.D.";
        $umidita_corrente="N.D.";
        while ($value = $result->fetch_assoc())
        {
          if($i==0)
          {
            $temperatura_corrente=$value['temperatura'];
            $pressione_corrente=$value['pressione']/100;
            $umidita_corrente=$value['umidita'];

            $data_adesso=$value['data']." ".$value['ora'];

            $format = "Y-m-d H:i:s";
            $dateobj = DateTime::createFromFormat($format, $data_adesso);
            $minuti=date("i",time()-$dateobj->getTimestamp());
          }



          $temperatura[$i]=$value['temperatura'];
          $pressione[$i]=$value['pressione']/100;
          $umidita[$i]=$value['umidita'];
          $i++;
        }
        if($i==0)
        {
          $temp_max="N.D.";
          $temp_min="N.D.";
          $umi_max="N.D.";
          $umi_min="N.D.";
          $press_max="N.D.";
          $press_min="N.D.";
          $previsione_giorno="N.D.";
        }
        else
        {
          if($minuti>=1)
          {
            $temperatura_corrente="X ";
            $pressione_corrente="X ";
            $umidita_corrente="X ";
          }
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
          // Calcolo la previsone giornaliera
          if ($media_pres>=1015)
          {
            $previsione_giorno="SOLEGGIATO";
          }
          else if ($media_pres>1008)
          {
            $previsione_giorno="VARIABILE";
          }
          else
          {
            if(($temp_max+$temp_min)/2>=0&&($temp_max+$temp_min)/2<=4)
            {
              $previsione_giorno="NEVOSO";
            }
            else
            {
              $previsione_giorno="PIOVOSO";
            }
          }
        }
      
       if($temp_max!="N.D.")
       {
       		 $params=[
                  'chat_id'=>$chatId,
                  'text'=>"Ecco i dati principali raccolti oggi\n\n*TEMPERATURA*\nAttuale: ".$temperatura_corrente."°C\nMassima: ".$temp_max."°C\nMinima: ".$temp_min."°C\n\n*UMIDITA*\nAttuale: ".$umidita_corrente."%\nMassima: ".$umi_max."%\nMinima: ".$umi_min."%\n\n*PRESSIONE*\nAttuale: ".$pressione_corrente."mB\nMassima: ".$press_max."mB\nMinima: ".$press_min."mB\n\n*Il tempo risulta $previsione_giorno*",
                  'parse_mode'=>'markdown',
                ];
              $ch = curl_init($website . '/sendMessage');
              curl_setopt($ch, CURLOPT_HEADER, false);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
              curl_setopt($ch, CURLOPT_POST, 1);
              curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
              $result = curl_exec($ch);
              curl_close($ch);
       }
       
    }
    $conn->close();
?>