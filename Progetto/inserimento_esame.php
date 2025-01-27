<?php
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

?>

<!doctype html>
<html lang="en">
  <head>
    <title>Inserimento Esame</title>
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

    <?php
        $ris_form="";
        if (!isset($_SESSION['form'])) {
            $_SESSION['form'] = bin2hex(random_bytes(16));
        }
        if (isset($_POST['form']) && $_POST['form'] === $_SESSION['form']) {
            $err="";
            $nome_esame=$data_esame=$id_insegnamento="";

            if(isset($_POST) && isset($_POST['nome_esame']) && isset($_POST['data_esame']) && isset($_POST['id_insegnamento'])){
                $nome_esame=$_POST['nome_esame'];
                $data_esame=$_POST['data_esame'];
                $id_insegnamento=$_POST['id_insegnamento'];
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

            $ris_form=pg_query($db,"SET SEARCH_PATH TO Unidb");
            $sql="INSERT INTO \"Unidb\".esame(data_esame,id_insegnamento,id_docente,nome_esame)
            VALUES ($1,$2,$3,$4)";

            $parametri=array(
                $data_esame,
                $id_insegnamento,
                $id_docente,
                $nome_esame
            );

            if(isset($_POST) && isset($_POST['nome_esame']) && isset($_POST['data_esame']) && isset($_POST['id_insegnamento'])){
            $risultato=pg_prepare($db,"ins_esame",$sql);
            $risultato=pg_execute($db,"ins_esame",$parametri);   
            }
            unset($_SESSION['form']);
        }
    ?>

    <div class="container mt-5">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <h3 style="text-align:center;" class="mb-3">Inserimento Nuovo Esame</h3>
            <div class="form-group">
                <label>Data Esame (Y/M/D)</label>
                <input type="text" class="form-control" name="data_esame" placeholder="Inserire Data... (Y/M/D)">
            </div>
            <div class="form-group">
                <label>ID Insegnamento</label>
                <input type="text" class="form-control" name="id_insegnamento"  placeholder="Inserire ID Insegnamento...">
            </div>
            <div class="form-group">
                <label>Nome Esame</label>
                <input type="text" class="form-control" name="nome_esame" placeholder="Inserire Nome...">
            </div>
            <input type="hidden" name="form" value="<?php echo $_SESSION['form']; ?>">
            <button type="submit" class="btn btn-primary btn-block">Inserisci Esame</button>
        </form>
    </div>


    <?php
        if($ris_form && empty(pg_last_error($db))){
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