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
?>


<!doctype html>
<html lang="en">
  <head>
    <title>Gestione Insegnamenti</title>
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
        $risultato_ins="";
        if (!isset($_SESSION['form_ins'])) {
            $_SESSION['form_ins'] = bin2hex(random_bytes(16));
        }
        
        if (isset($_POST['form_ins']) && $_POST['form_ins'] === $_SESSION['form_ins']) {
            $err="";
            $id=$nome=$id_docente=$anno=$descrizione="";

            if(isset($_POST['id_ins']) && isset($_POST['nome_ins']) && isset($_POST['docente_ins']) && isset($_POST['anno_ins']) && isset($_POST['desc_ins'])){
                if(strlen($_POST['id_ins'])==4 && (intval($_POST['anno_ins'])>0 && intval($_POST['anno_ins'])<4) && is_numeric($_POST['docente_ins']) && strlen($_POST['docente_ins'])==6){
                    $id=$_POST['id_ins'];
                    $id_docente=$_POST['docente_ins'];
                    $nome=$_POST['nome_ins'];
                    $anno=$_POST['anno_ins'];
                    $descrizione=$_POST['desc_ins'];
                }
            }else{
                $err="Dati inseriti non validi";
            }

            if(empty($err)){
                $db=connessione();
            }
            if(!$db){
                echo("Errore nella connessione.");
                chiusura_connessione($db);
                return;
            }

            $risultato_ins=pg_query($db,"SET SEARCH_PATH TO Unidb");
            $sql="INSERT INTO \"Unidb\".insegnamento(id,id_responsabile,anno,nome,descrizione)
            VALUES ($1,$2,$3,$4,$5);";

            $parametri=array(
                $id,
                $id_docente,
                $anno,
                $nome,
                $descrizione
            );

            echo($err);
            if(empty($err)){
                $risultato_ins=pg_prepare($db,"ins_insegnamento",$sql);
                $risultato_ins=pg_execute($db,"ins_insegnamento",$parametri);
            }            
                unset($_SESSION['form_ins']);
            }

    ?>


    <div class="container mt-5 border">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <h3 style="text-align:center;" class="mt-3 mb-3">Crea Insegnamento</h3>
            <div class="form-group">
                <label>ID Insegnamento</label>
                <input type="text" class="form-control" name="id_ins" placeholder="Inserire ID Insegnamento...">
            </div>
            <div class="form-group">
                <label>ID Docente Responsabile</label>
                <input type="text" class="form-control" name="docente_ins"  placeholder="Inserire ID Docente...">
            </div>
            <div class="form-group">
                <label>Nome</label>
                <input type="text" class="form-control" name="nome_ins" placeholder="Inserire Nome Insegnamento...">
            </div>
            <div class="form-group">
                <label>Anno</label>
                <input type="text" class="form-control" name="anno_ins" placeholder="Inserire Anno...">
            </div>
            <div class="form-group">
                <label>Descrizione</label>
                <input type="text" class="form-control" name="desc_ins" placeholder="Inserire Descrizione...">
            </div>
            <input type="hidden" name="form_ins" value="<?php echo $_SESSION['form_ins']; ?>">
            <button type="submit" class="btn btn-primary btn-block mt-3 mb-3">Crea Insegnamento</button>
        </form>
    </div>
    
    <?php
        if($risultato_ins && empty(pg_last_error($db))){
            echo("Creazione Effettuata!");
        }else{
            $err=pg_last_error($db);
            echo($err);
        }
    ?>

    
    <?php
        $risultato_mod="";

        if (!isset($_SESSION['form_mod'])) {
            $_SESSION['form_mod'] = bin2hex(random_bytes(16));
        }
        
        if (isset($_POST['form_mod']) && $_POST['form_mod'] === $_SESSION['form_mod']) {
            $err="";
            $id=$nome=$anno=$descrizione="";

            if(isset($_POST['id_mod']) && isset($_POST['nome_mod']) && isset($_POST['anno_mod']) && isset($_POST['desc_mod'])){
                if(strlen($_POST['id_mod'])==4 && (intval($_POST['anno_mod'])>0 && intval($_POST['anno_mod'])<4)){
                    $id=$_POST['id_mod'];
                    $nome=$_POST['nome_mod'];
                    $anno=$_POST['anno_mod'];
                    $descrizione=$_POST['desc_mod'];
                }else{
                    $err="Dati inseriti non validi";
                }
            }

            if(empty($err)){
                $db=connessione();
                if(!$db){
                    echo("Errore nella connessione!");
                    return;
                }
            }else{
                echo($err);
            }

            $risultato_mod=pg_query($db,"SET SEARCH_PATH TO Unidb");
            $sql="UPDATE \"Unidb\".insegnamento
            SET nome=$1,anno=$2,descrizione=$3
            WHERE id=$4";
            
            $parametri=array(
                $nome,
                $anno,
                $descrizione,
                $id
            );

            if(empty($err)){
                $risultato_mod=pg_prepare($db,"update_insegnamento",$sql);
                $risultato_mod=pg_execute($db,"update_insegnamento",$parametri);
            }
            unset($_SESSION['form_mod']);
        }
    ?>

    <div class="container mt-5 border">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <h3 style="text-align:center;" class="mt-3 mb-3">Aggiorna Dati Insegnamento</h3>
            <h5 style="text-align:center;" class="mt-3 mb-3">(Per i campi che non si vogliono modificare occorre rieffettuare l'inserimento)</h5> 
            <div class="form-group">
                <label>ID Insegnamento Da Modificare</label>
                <input type="text" class="form-control" name="id_mod" placeholder="Inserire ID Insegnamento...">
            </div>
            <div class="form-group">
                <label>Nome</label>
                <input type="text" class="form-control" name="nome_mod" placeholder="Inserire Nuovo Nome...">
            </div>
            <div class="form-group">
                <label>Anno</label>
                <input type="text" class="form-control" name="anno_mod"  placeholder="Inserire Nuovo Anno...">
            </div>
            <div class="form-group">
                <label>Descrizione</label>
                <input type="text" class="form-control" name="desc_mod" placeholder="Inserire Nuova Descrizione...">
            </div>
            <input type="hidden" name="form_mod" value="<?php echo $_SESSION['form_mod']; ?>">
            <button type="submit" class="btn btn-primary btn-block mt-3 mb-3">Modifica Insegnamento</button>
        </form>
    </div>

    <?php
        if($risultato_mod && empty(pg_last_error($db))){
            echo("Modifica Avvenuta!");
        }else{
            echo(pg_last_error($db));
        }
    ?>

<?php
        $risultato_del="";

        if (!isset($_SESSION['form_del'])) {
            $_SESSION['form_del'] = bin2hex(random_bytes(16));
        }
        
        if (isset($_POST['form_del']) && $_POST['form_del'] === $_SESSION['form_del']){
            $err="";
            $id="";

            if(isset($_POST['id_del'])){
                if(strlen($_POST['id_del'])==4){
                    $id=$_POST['id_del'];
                }else{
                    $err="Dati inseriti non validi";
                }
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

            $risultato_del=pg_query($db,"SET SEARCH_PATH TO Unidb");
            $sql="DELETE
            FROM \"Unidb\".insegnamento
            WHERE id=$1";
            
            $parametri=array(
                $id
            );

            if(empty($err)){
                $risultato_del=pg_prepare($db,"delete_insegnamento",$sql);
                $risultato_del=pg_execute($db,"delete_insegnamento",$parametri);
            }
            unset($_SESSION['form_del']);
        }
    ?>

    <div class="container mt-5 mb-5 border">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <h3 style="text-align:center;" class="mt-3 mb-3">Rimozione Insegnamento</h3>
            <h6>Attenzione! La rimozione dell'insegnamento ne comporta la rimozione dai corsi di laurea, la cancellazione degli esami e delle iscrizioni relative ad esso.<br> Le carriere degli studenti verranno lasciate intatte.</h6>
            <div class="form-group">
                <label>ID Insegnamento Da Rimuovere</label>
                <input type="text" class="form-control" name="id_del" placeholder="Inserire ID Insegnamento...">
            </div>
            <input type="hidden" name="form_del" value="<?php echo $_SESSION['form_del']; ?>">
            <button type="submit" class="btn btn-primary btn-block mt-3 mb-3">Rimuovi Insegnamento</button>
        </form>
    </div>

    <?php
        if($risultato_del && empty(pg_last_error($db))){
            echo("Cancellazione Effettuata!");
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