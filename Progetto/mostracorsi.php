<?php
    ini_set ("display_errors", "On");
    ini_set("error_reporting", E_ALL);
    $error_msg = '';
    $success_msg = '';
    include_once("utils/funzioni.php");
    
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
    if (isset($_SESSION['utente'])){
        $loggato=$_SESSION['utente'];
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

    $risultato=pg_query($db,"SET SEARCH_PATH TO Unidb");
    

    $sql="SELECT * from \"Unidb\".corso";
    $parametri=Array();
    $risultato=pg_prepare($db,"corsi",$sql);
    $risultato=pg_execute($db,"corsi",$parametri);

    $corsi=Array();

    if($risultato){
        while($row=pg_fetch_assoc($risultato)){
            $id=$row['id'];
            $nome=$row['nome'];
            $tipo=$row['tipo'];

            $corsi[$id]=array($nome,$tipo);
        }
    }

?>

<!doctype html>
<html lang="en">
  <head>
    <title>Corsi</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="stile.css">
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
    <div class="container mt-5 border">
        <h3 class="text-center">Corsi Disponibili</h3>
        </br>
        <?php 
        // In caso di mancanza di risultati
        if (count($corsi) == 0) {?>
            <div class="alert alert-warning" role="alert">
                <p>Nessun corso trovato</p>
            </div>
        <?php }
        // Intestazione tabella
        else {?>
            <table class="table table-striped-columns">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome Del Corso</th>
                    <th>Triennale/Magistrale</th>
                </tr>
            </thead>
            <tbody>
        <?php
        /*
         * Visualizzo i record
         */
        foreach($corsi as $id=>$valori){
        ?>
            <tr>
                <td><?php echo $id; ?></td>
                <td><?php echo $valori[0]; ?></td>
                <td><?php echo $valori[1]; ?></td>
            </tr>
        <?php
        }
        ?>
        </tbody>
        </table>

        <?php 
        }
        chiusura_connessione($db);
        }
        ?>
    <!--Javascript Bootstrap-->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>