<?php 
    ini_set("display_errors", "On");
    ini_set("error_reporting", E_ALL);
    include_once('utils/funzioni.php');  
?>

<!doctype html>
<html lang="en">
  <head>
    <title>Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="stile-login.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  </head>
  
  <body>
      <nav class="navbar navbar-expand-lg navbar-light bg-light mb-5">
        <a class="navbar-brand">Progetto Basi di Dati</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
    </nav>
    
    <div class="container">
        <h1 style="color:black;" >Progetto Basi di Dati</h1>
        <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="login-container">
            <h3 class="text-center mb-4 ">Login</h3>
            <form action="dashboard.php" method="POST">
                <div class="form-group">
                <label for="nome_utente">Nome Utente</label>
                <input type="text" class="form-control" id="utente-utente" name="utente" placeholder="Nome Utente">
                </div>
                <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" id="utente-password" placeholder="Password">
                </div>
                <div class="form group">
                <label>Tipo Utente</label>
                <select class="custom-select mb-4" name="tipo">
                    <option value="Docente">Docente</option>
                    <option value="Segreteria">Segreteria</option>
                    <option value="Studente">Studente</option>
                </select>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            </div>
        </div>
        </div>
    </div>



    <!-- Javascript Bootstrap-->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>