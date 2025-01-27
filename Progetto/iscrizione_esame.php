<?php
    ini_set ("display_errors", "On");
    ini_set("error_reporting", E_ALL);
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
        chiusura_connessione($db);
    }

    $risultato=pg_query($db,"SET SEARCH_PATH TO Unidb");

    //recupero matricola studente loggato
    $matricola="";
    $sql="SELECT matricola FROM \"Unidb\".studente WHERE nome_utente=$1";

    $parametri=array(
        $loggato
    );

    $risultato=pg_prepare($db,"matricola",$sql);
    $risultato=pg_execute($db,"matricola",$parametri);
    if($risultato){
        while($row=pg_fetch_assoc($risultato)){
            $matricola=$row['matricola'];
        }
    }

    //recupero id_corso dello studente, in modo tale da mostrare solo gli esami appartenenti al corso dello studente
    $id_corso="";
    $sql="SELECT id_corso FROM \"Unidb\".studente WHERE matricola=$1;";
    $parametri=array(
        $matricola
    );

    $risultato=pg_prepare($db,"id_corso",$sql);
    $risultato=pg_execute($db,"id_corso",$parametri);
    if($risultato){
        while($row=pg_fetch_assoc($risultato)){
            $id_corso=$row['id_corso'];
        }
    }

    // recupero esami degli insegnamenti del corso dello studente
    $esami=array();

    $sql="SELECT data_esame,esame.id_insegnamento,esame.id_docente,nome_esame
    FROM \"Unidb\".esame,\"Unidb\".\"Unidb\".composizione
    WHERE composizione.id_corso=$1 AND esame.id_insegnamento=composizione.id_insegnamento and data_esame>current_date
    ORDER BY data_esame;";

    $parametri=array(
        $id_corso
    );
    $risultato=pg_prepare($db,"vis_esami",$sql);
    $risultato=pg_execute($db,"vis_esami",$parametri);
    if($risultato){
        $i=0;
        while($row=pg_fetch_assoc($risultato)){
            $data_esame=$row['data_esame'];
            $id_insegnamento=$row['id_insegnamento'];
            $id_docente=$row['id_docente'];
            $nome_esame=$row['nome_esame'];

            $esami[$i]=array($data_esame,$nome_esame,$id_docente,$id_insegnamento);
            $i++;
        }
    }

?>

<!doctype html>
<html lang="en">
  <head>
    <title>Iscrizione</title>
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
        <h3 class="text-center">Esami Disponibili</h3>
        </br>
        <?php 
        // In caso di mancanza di risultati
        if (count($esami) == 0) {?>
            <div class="alert alert-warning" role="alert">
                <p>Nessun esame trovato</p>
            </div>
        <?php }
        else {
        // Intestazione tabella
        ?>
            <table class="table table-striped-columns">
            <thead>
                <tr>
                    <th>Data Esame (Y/M/D)</th>
                    <th>Esame</th>
                    <th>ID Insegnamento</th>
                </tr>
            </thead>
            <tbody>
        <?php
        
        // Visualizzo i record
        foreach($esami as $i=>$valori){
        ?>
            <tr>
                <td><?php echo $valori[0]; ?></td>
                <td><?php echo $valori[1]; ?></td>
                <td><?php echo $valori[3]; ?></td>
            </tr>
        <?php
        }
        ?>
        </tbody>
        </table>
    </div>


    <?php
        $ris="";

        if (!isset($_SESSION['selezione'])) {
            $_SESSION['selezione'] = bin2hex(random_bytes(16));
        }
        if (isset($_POST['selezione']) && $_POST['selezione'] === $_SESSION['selezione']) {
            $err="";
            $id_insegnamento=$id_docente=$data_esame="";
            
            if(isset($_POST['esame']) && $_POST["esame"]!="-1"){
                $data_esame=$esami[$_POST["esame"]][0];
                $id_docente=$esami[$_POST["esame"]][2];
                $id_insegnamento=$esami[$_POST["esame"]][3];
            }else{
                $err="Selezionare un esame!";
            }
            if(empty($err)){
                $db=connessione();
            }
            if(!$db){
                echo("Errore nella connessione.");
                chiusura_connessione($db);
                return;
            }

            $ris=pg_query($db,"SET SEARCH_PATH TO Unidb");
            $sql="INSERT INTO \"Unidb\".iscrizione(matricola,id_corso,id_insegnamento,id_docente,data_esame)
            VALUES ($1,$2,$3,$4,$5);";

            $parametri=array(
                $matricola,
                $id_corso,
                $id_insegnamento,
                $id_docente,
                $data_esame
            );

            if(empty($err)){
            $ris=pg_prepare($db,"ins_iscrizione",$sql);
            $ris=pg_execute($db,"ins_iscrizione",$parametri);
            }  
            unset($_SESSION['selezione']);
        }
    ?>

    <div class="container mt-5 border">
        <form action="<?php echo($_SERVER['PHP_SELF']); ?>" method="POST">
            <div class="input-group mt-3 mb-3">
                <select class="custom-select mb-3" name="esame">
                    <option value="-1" selected>Seleziona Esame...</option>
                    <?php
                        foreach($esami as $i=>$valori){                    
                    ?>
                        <option value="<?php echo("$i"); ?>"><?php echo("$valori[0],$valori[1],$valori[3]");?></option>
                <?php
                    $i++;
                    }
                ?>
                </select>
                <input type="hidden" name="selezione" value="<?php echo $_SESSION['selezione']; ?>">
                <br>
                <button type="submit" class="btn btn-primary btn-block">Iscriviti</button>
            </div>
        </form>
    </div>
    <?php 
        if($ris && empty(pg_last_error($db))){
            echo("Iscrizione Effettuata!");
        }else{
            echo(pg_last_error($db));
        }
        }
        chiusura_connessione($db);
        }
    ?>


    <!-- Javascript Bootstrap-->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>