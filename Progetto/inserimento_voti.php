<?php
    ini_set("display_errors", "On");
    ini_set("error_reporting", E_ALL);
    include_once('utils/funzioni.php');

    $loggato=null;
    $data_esame=null;
    $id_insegnamento=null;
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
    if(isset($_SESSION['utente'])){
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


    if(isset($_POST) && isset($_POST['data_esame']) && isset($_POST['id_insegnamento']) && strlen($_POST['id_insegnamento'])==4){
        $data_esame=$_POST['data_esame'];
        $id_insegnamento=$_POST['id_insegnamento'];
    }
    if(isset($_SESSION['data_esame']) && isset($_SESSION['id_insegnamento'])){
        $data_esame=$_SESSION['data_esame'];
        $id_insegnamento=$_SESSION['id_insegnamento'];
    }

    if(isset($data_esame) && isset($id_insegnamento)){
        $_SESSION['data_esame']=$data_esame;
        $_SESSION['id_insegnamento']=$id_insegnamento;
    }

    $db=connessione();
    if(!$db){
        echo("Connessione non riuscita!");
        return;
    }
    $risultato=pg_query($db,"SET SEARCH_PATH TO Unidb");

    //recupero id docente loggato
    $id_docente="";
    $sql="SELECT id FROM \"Unidb\".docente WHERE nome_utente=$1;";
    
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

    // recupero iscritti all'esame
    $iscritti=array();
    $sql="SELECT matricola,id_corso
    FROM \"Unidb\".iscrizione
    WHERE id_docente=$1 AND data_esame=$2 AND id_insegnamento=$3;";

    $parametri=array(
        $id_docente,
        $data_esame,
        $id_insegnamento
    );

    $risultato=pg_prepare($db,"vis_iscritti",$sql);
    $risultato=pg_execute($db,"vis_iscritti",$parametri);
    if($risultato){
        $i=0;
        while($row=pg_fetch_assoc($risultato)){
            $matricola=$row['matricola'];
            $id_corso=$row['id_corso'];

            $iscritti[$i]=array($matricola,$id_corso);
            $i++;
        }
    }
    ?>


<!doctype html>
<html lang="en">
  <head>
    <title>Inserimento Voto</title>
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
            <h3 class="text-center">Iscritti Esame</h3>
            </br>
            <?php 
            // In caso di mancanza di risultati
            if (count($iscritti) == 0) {?>
                <div class="alert alert-warning" role="alert">
                    <p>Nessun iscritto corrispondente ai dati inseriti</p>
                </div>
            <?php }
            // Intestazione tabella
            else {?>
                <table class="table table-striped-columns">
                <thead>
                    <tr>
                        <th>Matricola</th>
                        <th>ID Corso</th>
                    </tr>
                </thead>
                <tbody>
            <?php
            // Visualizzo i record
            foreach($iscritti as $i=>$valori){
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
        </div>
            
        <?php 
        }
        
        $ris="";

        if (!isset($_SESSION['selezione'])) {
            $_SESSION['selezione'] = bin2hex(random_bytes(16));
        }
        if (isset($_POST['selezione']) && $_POST['selezione'] === $_SESSION['selezione']) {

            $matricola="";
            $id_corso="";
            if(isset($_POST) && isset($_POST['studente']) && $_POST['studente']!="-1"){
                $matricola=$iscritti[$_POST['studente']][0];
                $id_corso=$iscritti[$_POST['studente']][1];
            }else{
                $err="selezionare uno studente";
            }

            if(empty($err)){
                $db=connessione();
            }
            if(!$db){
                echo("Connessione non riuscita");
                return;
            }

            $voto="";
            if(isset($_POST) && isset($_POST['voto']) && is_numeric($_POST['voto'])){
                $voto=intval($_POST['voto']);
            }

            $ris=pg_query($db,"SET SEARCH_PATH TO Unidb");

            $sql="INSERT INTO \"Unidb\".carriera(matricola,id_corso,id_insegnamento,id_docente,data_esame,voto)
            VALUES ($1,$2,$3,$4,$5,$6);";

            $parametri=array(
                $matricola,
                $id_corso,
                $id_insegnamento,
                $id_docente,
                $data_esame,
                $voto
            );
            
            if(empty($err)){
                $ris=pg_prepare($db,"ins_voto",$sql);
                $ris=pg_execute($db,"ins_voto",$parametri);
            }
            unset($_SESSION['selezione']);
        }
        ?>

        <div class="container mt-5 border">
            <form action="<?php echo($_SERVER['PHP_SELF']); ?>" method="POST">
                <h3 style="text-align:center;" class="mb-4">Selezione Studente</h3>
                <div class="input-group mt-3 mb-3 justify-content-center">
                    <div class="form-group">
                    <select class="custom-select mb-3" name="studente">
                        <option value="-1" selected>Seleziona Studente...</option>
                        <?php
                            foreach($iscritti as $i=>$valori){                    
                        ?>
                            <option value="<?php echo("$i"); ?>"><?php echo("$valori[0], $valori[1]");?></option>
                    <?php
                        $i++;
                        }
                    ?>
                    </select>
                    <div class="form-group">
                        <label>Voto</label>
                        <input type="text" class="form-control" name="voto" placeholder="Inserire Voto...">
                    </div>
                    <input type="hidden" name="selezione" value="<?php echo $_SESSION['selezione']; ?>">
                    <button type="submit" class="btn btn-primary btn-block">Inserisci</button>
                </div>
            </form>
        </div>    
        
        <?php
         if($ris && empty(pg_last_error($db))){
            echo("Inserimento Effettuato!");
        }else{
            $err=pg_last_error($db);
            echo($err);
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