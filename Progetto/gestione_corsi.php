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
            $id=$nome=$tipo="";

            if(isset($_POST['id_ins']) && isset($_POST['nome_ins']) && isset($_POST['tipo_ins'])){
                if(strlen($_POST['id_ins'])==6){
                    $id=$_POST['id_ins'];
                    $nome=$_POST['nome_ins'];
                    $tipo=$_POST['tipo_ins'];
                }
            }else{
                $err="Dati inseriti non validi";
            }

            if(empty($err)){
                $db=connessione();
            }else{
                echo($err);
            }
            if(!$db){
                echo("Errore nella connessione.");
                chiusura_connessione($db);
                return;
            }

            $risultato_ins=pg_query($db,"SET SEARCH_PATH TO Unidb");
            $sql="INSERT INTO \"Unidb\".corso(id,nome,tipo)
            VALUES ($1,$2,$3);";

            $parametri=array(
                $id,
                $nome,
                $tipo
            );

            if(empty($err)){
                $risultato_ins=pg_prepare($db,"ins_corso",$sql);
                $risultato_ins=pg_execute($db,"ins_corso",$parametri);
            }            
                unset($_SESSION['form_ins']);
            }
    ?>

    <div class="container mt-5 border">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <h3 style="text-align:center;" class="mt-3 mb-3">Crea Corso</h3>
            <div class="form-group">
                <label>ID Corso</label>
                <input type="text" class="form-control" name="id_ins" placeholder="Inserire ID Corso (massimo 6 caratteri)...">
            </div>
            <div class="form-group">
                <label>Nome</label>
                <input type="text" class="form-control" name="nome_ins" placeholder="Inserire Nome Corso...">
            </div>
            <select class="custom-select mb-4" name="tipo_ins">
                    <option value="Triennale">Triennale</option>
                    <option value="Magistrale">Magistrale</option>
            </select>
            <input type="hidden" name="form_ins" value="<?php echo $_SESSION['form_ins']; ?>">
            <button type="submit" class="btn btn-primary btn-block mt-3 mb-3">Crea Corso</button>
        </form>
    </div>
    
    <?php
        if($risultato_ins){
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
            $id=$nome="";

            if(isset($_POST['id_mod']) && isset($_POST['nome_mod'])){
                if(strlen($_POST['id_mod'])==6){
                    $id=$_POST['id_mod'];
                    $nome=$_POST['nome_mod'];
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
            $sql="UPDATE \"Unidb\".corso
            SET nome=$1
            WHERE id=$2";
            
            $parametri=array(
                $nome,
                $id
            );

            if(empty($err)){
                $risultato_mod=pg_prepare($db,"update_corso",$sql);
                $risultato_mod=pg_execute($db,"update_corso",$parametri);
            }
            unset($_SESSION['form_mod']);
        }
    ?>

    <div class="container mt-5 border">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <h3 style="text-align:center;" class="mt-3 mb-3">Aggiorna Dati Corso</h3>
            <h5 style="text-align:center;" class="mt-3 mb-3">(Per i campi che non si vogliono modificare occorre rieffettuare l'inserimento)</h5> 
            <div class="form-group">
                <label>ID Corso Da Modificare</label>
                <input type="text" class="form-control" name="id_mod" placeholder="Inserire ID Corso (massimo 6 caratteri)...">
            </div>
            <div class="form-group">
                <label>Nome</label>
                <input type="text" class="form-control" name="nome_mod" placeholder="Inserire Nuovo Nome...">
            </div>
            <input type="hidden" name="form_mod" value="<?php echo $_SESSION['form_mod']; ?>">
            <button type="submit" class="btn btn-primary btn-block mt-3 mb-3">Modifica Corso</button>
        </form>
    </div>

    <?php
        if($risultato_mod){
            echo("Modifica Avvenuta!");
        }else{
            echo(pg_last_error($db));
        }
    ?>

    <?php
        $risultato_insc="";

        if (!isset($_SESSION['form_insc'])) {
            $_SESSION['form_insc'] = bin2hex(random_bytes(16));
        }
        
        if (isset($_POST['form_insc']) && $_POST['form_insc'] === $_SESSION['form_insc']) {
            $err="";
            $id_corso=$id_insegnamento=$id_docente="";

            if(isset($_POST['id_corso_insc']) && isset($_POST['id_ins_insc']) && isset($_POST['id_docente_insc']) ){
                if(strlen($_POST['id_corso_insc'])==6 && strlen($_POST['id_ins_insc'])==4){
                    $id_corso=$_POST['id_corso_insc'];
                    $id_insegnamento=$_POST['id_ins_insc'];
                    $id_docente=$_POST['id_docente_insc'];
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

            $risultato_insc=pg_query($db,"SET SEARCH_PATH TO Unidb");
            $sql="INSERT INTO \"Unidb\".composizione(id_corso,id_insegnamento,id_docente)
            VALUES ($1,$2,$3)";
            
            $parametri=array(
                $id_corso,
                $id_insegnamento,
                $id_docente
            );

            if(empty($err)){
                $risultato_insc=pg_prepare($db,"ins_insegnamento_in_corso",$sql);
                $risultato_insc=pg_execute($db,"ins_insegnamento_in_corso",$parametri);
            }
            unset($_SESSION['form_insc']);
        }
    ?>

    
    <div class="container mt-5 border">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <h3 style="text-align:center;" class="mt-3 mb-3">Inserimento Insegnamenti In Corsi Di Laurea</h3>
            <div class="form-group">
                <label>ID Corso In Cui Inserire L'Insegnamento</label>
                <input type="text" class="form-control" name="id_corso_insc" placeholder="Inserire ID Corso (massimo 6 caratteri)...">
            </div>
            <div class="form-group">
                <label>Inserire ID Dell'Insegnamento Da Inserire</label>
                <input type="text" class="form-control" name="id_ins_insc" placeholder="Inserire ID Insegnamento (massimo 4 caratteri)...">
            </div>
            <div class="form-group">
                <label>Inserire ID Del Docente Responsabile</label>
                <input type="text" class="form-control" name="id_docente_insc" placeholder="Inserire ID Docente (massimo 6 caratteri numerici)...">
            </div>
            <input type="hidden" name="form_insc" value="<?php echo $_SESSION['form_insc']; ?>">
            <button type="submit" class="btn btn-primary btn-block mt-3 mb-3">Inserisci Insegnamento In Corso</button>
        </form>
    </div>


    <?php
        if($risultato_insc){
            echo("Inserimento Avvenuto!");
        }else{
            echo(pg_last_error($db));
        }
    ?>

<?php
        $risultato_prop="";

        if (!isset($_SESSION['form_prop'])) {
            $_SESSION['form_prop'] = bin2hex(random_bytes(16));
        }
        
        if (isset($_POST['form_prop']) && $_POST['form_prop'] === $_SESSION['form_prop']) {
            $err="";
            $id_ins_a=$id_ins_b="";

            if(isset($_POST['id_ins_a']) && isset($_POST['id_ins_b'])){
                if(strlen($_POST['id_ins_a'])==4 && strlen($_POST['id_ins_b'])==4){
                    $id_ins_a=$_POST['id_ins_a'];
                    $id_ins_b=$_POST['id_ins_b'];
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

            $risultato_prop=pg_query($db,"SET SEARCH_PATH TO Unidb");
            $sql="INSERT INTO \"Unidb\".propedeuticita(insegnamento_a,insegnamento_b)
            VALUES ($1,$2)";
            
            $parametri=array(
                $id_ins_a,
                $id_ins_b
            );

            if(empty($err)){
                $risultato_prop=pg_prepare($db,"ins_propedeuticita",$sql);
                $risultato_prop=pg_execute($db,"ins_propedeuticita",$parametri);
            }
            unset($_SESSION['form_prop']);
        }
    ?>

    
    <div class="container mt-5 border">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <h3 style="text-align:center;" class="mt-3 mb-3">Inserimento Propedeuticità</h3>
            <div class="form-group">
                <label>ID Insegnamento Propedeutico</label>
                <input type="text" class="form-control" name="id_ins_a" placeholder="Inserire ID Insegnamento (massimo 4 caratteri)...">
            </div>
            <div class="form-group">
                <label>Inserire ID Insegnamento Con Propedeuticità</label>
                <input type="text" class="form-control" name="id_ins_b" placeholder="Inserire ID Insegnamento (massimo 4 caratteri)...">
            </div>
            <input type="hidden" name="form_prop" value="<?php echo $_SESSION['form_prop']; ?>">
            <button type="submit" class="btn btn-primary btn-block mt-3 mb-3">Inserisci Propedeuticità</button>
        </form>
    </div>


    <?php
        if($risultato_prop){
            echo("Inserimento Avvenuto!");
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