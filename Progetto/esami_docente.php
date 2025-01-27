<?php
    ini_set("display_errors", "On");
    ini_set("error_reporting", E_ALL);
    include_once('utils/funzioni.php');

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
    
    //recupero id docente loggato
    $id_docente="";
    $sql="SELECT id FROM \"Unidb\".docente WHERE nome_utente=$1";
    
    $parametri=array(
        $loggato
    );

    $risultato=pg_prepare($db,"id_docente",$sql);
    $risultato=pg_execute($db,"id_docente",$parametri);
    if($risultato){
        while($row=pg_fetch_assoc($risultato)){
            $id_docente=$row['id'];
        }
    }

    // Recupero esami del docente loggato
    $esami_docente=array();
    $sql="SELECT esame.data_esame, esame.id_insegnamento, esame.id_docente, esame.nome_esame
    FROM \"Unidb\".esame
    WHERE id_docente=$1
    order by data_esame;";

    $parametri=array(
        $id_docente
    );

    $risultato=pg_prepare($db,"vis_esami_docente",$sql);
    $risultato=pg_execute($db,"vis_esami_docente",$parametri);
    if($risultato){
        $i=0;
        while($row=pg_fetch_assoc($risultato)){
            $data_esame=$row['data_esame'];
            $id_insegnamento=$row['id_insegnamento'];
            $id_docente=$row['id_docente'];
            $nome_esame=$row['nome_esame'];
            
            $esami_docente[$i]=array($data_esame,$nome_esame,$id_insegnamento,$id_docente);
            $i++;
        }
    }
?>


<!doctype html>
<html lang="en">
  <head>
    <title>Esami Docente</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  </head>
  <body>

    <?php
        include_once("utils/navbar.php");
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
        <h3 class="text-center">Esami Docente</h3>
        </br>
        <?php 
        // In caso di mancanza di risultati
        if (count($esami_docente) == 0) {?>
            <div class="alert alert-warning" role="alert">
                <p>Nessun esame corrispondente al docente</p>
            </div>
        <?php }
        // Intestazione tabella
        else {?>
            <table class="table table-striped-columns">
            <thead>
                <tr>
                    <th>Data Esame Y/M/D</th>
                    <th>Esame</th>
                    <th>ID Insegnamento</th>
                    <th>ID Docente</th>
                </tr>
            </thead>
            <tbody>
        <?php
        /*
         * Visualizzo i record
         */
        foreach($esami_docente as $i=>$valori){
        ?>
            <tr>
                <td><?php echo $valori[0]; ?></td>
                <td><?php echo $valori[1]; ?></td>
                <td><?php echo $valori[2]; ?></td>
                <td><?php echo $valori[3]; ?></td>
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