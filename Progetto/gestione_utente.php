<?php
    ini_set("display_errors", "On");
    ini_set("error_reporting", E_ALL);
    include_once('C:xampp/htdocs/Progetto/utils/funzioni.php');

    $loggato=null;
    session_start();
    $utente='';
    $password='';
    $tipo='';

    if (isset($_POST) && isset($_POST["utente"]) && isset($_POST["password"]) && isset($_POST["tipo"])){
        $utente=$_POST["utente"];
        $password=$_POST["password"];
        $tipo=$_POST["tipo"];
    }
    
    $messaggio_err='';
    if (isset($_POST) && isset($_POST["utente"]) && isset($_POST["password"])){
        $loggato=login($utente,md5($password),$tipo);
        if (is_null($loggato)){ //utente non trovato
            $messaggio_err='Utente non trovato. Controllare le credenziali e riprovare!';
        }
    }
        

    // imposto la variabile loggato se esiste una sessione aperta
    if (isset($_SESSION['utente']) && isset($_SESSION['tipo'])){
        $loggato=$_SESSION['utente'];
        $tipo=$_SESSION['tipo'];
    }

    //aggiorno la variabile di sessione
    if(isset($loggato)){
        $_SESSION['utente']=$loggato;
    }


    // se l'utente fa logout, inizializza $loggato
    // operazione per il logout, re-inizializzo loggato e tolgo l'utente dalla variabile globale SESSION
    if(isset($_GET) && isset($_GET['log']) && $_GET['log']=='canc'){
        unset($_SESSION['utente']);
        $loggato=null;
    }

    $db=connessione();
    if(!$db){
        print("Errore durante la connessione");
        return;
    }
?>

<!doctype html>
<html lang="en">
  <head>
    <title>Gestione Docenti</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  </head>
  <body>
  <?php
    include_once("C:xampp/htdocs/Progetto/utils/navbar.php");
    ?>

    <?php
        if(!isset($loggato)){
            header("location:login.php");
            exit;
    ?>
    
    <?php
    }else{
    ?>

<?php
        $risultato_mod="";

        if (!isset($_SESSION['form_mod'])) {
            $_SESSION['form_mod'] = bin2hex(random_bytes(16));
        }
        
        if (isset($_POST['form_mod']) && $_POST['form_mod'] === $_SESSION['form_mod']) {
            $err="";
            $nome_utente=$password_mod="";

            if(isset($_POST['nome_utente_mod']) && isset($_POST['password_mod'])){
                $nome_utente=$_POST['nome_utente_mod'];
                $password_mod=$_POST['password_mod'];
            }else{
                $err="Dati inseriti non validi";
            }

            if(empty($err)){
                $db=connessione();
                if(!$db){
                    echo("Errore nella connessione!");
                    return;
                }
            }else{
                echo($err);
                return;
            }

            $risultato_mod=pg_query($db,"SET SEARCH_PATH TO Unidb");
            $sql="";

            switch($tipo){
                case "Segreteria":
                    $sql="UPDATE \"Unidb\".segreteria
                    SET nome_utente=$1,password=$2
                    WHERE nome_utente=$3;";
                    break;
                
                case "Docente":
                    $sql="UPDATE \"Unidb\".docente
                    SET nome_utente=$1,password=$2
                    WHERE nome_utente=$3;";
                    break;
                
                case "Studente":
                    $sql="UPDATE \"Unidb\".studente
                    SET nome_utente=$1,password=$2
                    WHERE nome_utente=$3;";
                    break;
            }
            
            $parametri=array(
                $nome_utente,
                md5($password_mod),
                $loggato
            );

            if(empty($err)){
                $risultato_mod=pg_prepare($db,"update_utente",$sql);
                $risultato_mod=pg_execute($db,"update_utente",$parametri);
            }
            unset($_SESSION['form_mod']);
            
        }
    ?>

    <div class="container mt-5 border">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <h3 style="text-align:center;" class="mt-3 mb-3">Aggiorna Dati Utente</h3>
            <h5 style="text-align:center;" class="mt-3 mb-3">(Per i campi che non si vogliono modificare occorre rieffettuare l'inserimento)</h5> 
            <div class="form-group">
                <label>Nome Utente</label>
                <input type="text" class="form-control" name="nome_utente_mod" placeholder="Inserire Nuovo Nome Utente...">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" class="form-control" name="password_mod" placeholder="Inserire Nuova Password...">
            </div>
            <input type="hidden" name="form_mod" value="<?php echo $_SESSION['form_mod']; ?>">
            <button type="submit" class="btn btn-primary btn-block mt-3 mb-3">Modifica Utente</button>
        </form>
    </div>

    <?php
        if($risultato_mod && empty(pg_last_error($db))){
            $loggato=$nome_utente;
            echo("Modifica Avvenuta!");
        }else{
            echo(pg_last_error($db));
        }
    ?>

    <?php
    chiusura_connessione($db);
    }
    ?>

    <!--Javascript Bootstrap-->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>