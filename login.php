<?php
	session_start();
    $messaggio="";
    if(isset($_SESSION['messaggio']))
    {
   		$messaggio=$_SESSION['messaggio'];
    	$_SESSION['messaggio']="";
    }
?>
<html>
  <head>
  	  <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
      <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
      <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
      <link rel="stylesheet" type="text/css" href="stileLogin.css">
      <link rel="shortcut icon" type="image/png" href="img/logo.png"/>
      <title>Inserimento codice</title>
  </head>
  <body>
      <div class="container">
          <div class="card card-container">
              <img id="profile-img" class="profile-img-card" src="http://smartweatherstation.altervista.org/img/logo.png" />
              <hr style="width: 80%; border: 0.25px solid #aaa;">
              <form class="form-signin" action="loginCodice.php" method="post">
                  <input type="text" id="inputCodice" name="codice" class="form-control" placeholder="Codice della stazione..." required autofocus>
                  <button class="btn btn-lg btn-primary btn-block btn-signin" type="submit">Vai alla stazione</button>
              </form>
              <?php echo $messaggio?>
          </div>
      </div>
  </body>
</html>