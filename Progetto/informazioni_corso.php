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

    $id_corso="";
    if(isset($_POST['id_corso']) && strlen($_POST['id_corso'])==6){
        $id_corso=$_POST['id_corso'];
    }else{
        echo("ID inserito non valido");
    }

    $db=connessione();
    if(!$db){
        print("Errore durante la connessione");
        return;
    }

    $risultato=pg_query($db,"SET SEARCH_PATH TO Unidb");
    
    //recupero informazioni insegnamenti del corso
    $info_insegnamento=array();
    $sql="SELECT insegnamento.id,insegnamento.id_responsabile,insegnamento.anno,insegnamento.nome,insegnamento.descrizione
    FROM \"Unidb\".insegnamento, \"Unidb\".composizione
    WHERE composizione.id_corso=$1 AND insegnamento.id=composizione.id_insegnamento;";
    
    $parametri=array(
        $id_corso
    );

    $risultato=pg_prepare($db,"vis_info_corso",$sql);
    $risultato=pg_execute($db,"vis_info_corso",$parametri);

    if($risultato){
        $i=0;
        while($row=pg_fetch_assoc($risultato)){
            $id=$row['id'];
            $id_responsabile=$row['id_responsabile'];
            $nome=$row['nome'];
            $anno=$row['anno'];
            $descrizione=$row['descrizione'];

            $info_insegnamento[$i]=array($id,$id_responsabile,$nome,$anno,$descrizione);
            $i++;
        }
    }

    //recupero insegnamenti propedeutici
    $propedeutici=array();
    $sql="SELECT insegnamento_a, insegnamento_b
    FROM \"Unidb\".propedeuticita,\"Unidb\".composizione comp_a,\"Unidb\".composizione comp_b
    WHERE comp_a.id_corso=$1 AND insegnamento_a=comp_a.id_insegnamento AND comp_b.id_corso=$1
    AND comp_b.id_insegnamento=insegnamento_b;";

    $parametri=array(
        $id_corso
    );

    $risultato=pg_prepare($db,"vis_insegnamenti_prop",$sql);
    $risultato=pg_execute($db,"vis_insegnamenti_prop",$parametri);

    if($risultato){
        $i=0;
        while($row=pg_fetch_assoc($risultato)){
            $insegnamento_a=$row['insegnamento_a'];
            $insegnamento_b=$row['insegnamento_b'];
            $propedeutici[$i]=array($insegnamento_a,$insegnamento_b);
            $i++;
        }
    }
?>

<!doctype html>
<html lang="en">
  <head>
    <title>Informazioni Corso</title>
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
    <div class="container mt-5 border">
        <h3 class="text-center">Insegnamenti Del Corso</h3>
        </br>
        <?php 
        // In caso di mancanza di risultati
        if (count($info_insegnamento) == 0) {?>
            <div class="alert alert-warning" role="alert">
                <p>Nessun insegnamento relativo al corso inserito trovato</p>
            </div>
        <?php }
        // Intestazione tabella
        else {?>
            <table class="table table-striped-columns">
            <thead>
                <tr>
                    <th>ID Insegnamento</th>
                    <th>ID Responsabile</th>
                    <th>Nome</th>
                    <th>Anno</th>
                    <th>Descrizione</th>
                </tr>
            </thead>
            <tbody>
        <?php
        // Visualizzo i record
        foreach($info_insegnamento as $i=>$valori){
        ?>
            <tr>
                <td><?php echo $valori[0]; ?></td>
                <td><?php echo $valori[1]; ?></td>
                <td><?php echo $valori[2]; ?></td>
                <td><?php echo $valori[3]; ?></td>
                <td><?php echo $valori[4]; ?></td>
            </tr>
        <?php
        }
        }
        ?>
        </tbody>
        </table>


        <div class="container mt-5 border">
        <h3 class="text-center">Propedeuticità</h3>
        </br>
        <?php 
        // In caso di mancanza di risultati
        if (count($propedeutici) == 0) {?>
            <div class="alert alert-warning" role="alert">
                <p>Nessuna propedeuticità trovata</p>
            </div>
        <?php }
        // Intestazione tabella
        else {?>
            <table class="table table-striped-columns">
            <thead>
                <tr>
                    <th>ID Insegnamento Propedeutico</th>
                    <th>ID Insegnamento con Propedeuticità</th>
                </tr>
            </thead>
            <tbody>
        <?php
        // Visualizzo i record
        foreach($propedeutici as $i=>$valori){
        ?>
            <tr>
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