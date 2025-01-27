<?php
define ("myhost", "localhost");
define ("myuser", "postgres");
define ("mypsw", "india");
define ("mydb", "Unidb");
?>

<?php

// Apro la connessione
function connessione() {
    $connessione = "host=".myhost." dbname=".mydb." user=".myuser." password=".mypsw;
    return pg_connect($connessione);  
}


//funzione di login
function login($utente,$password,$tipo) {
    $loggato = null;

    $db = connessione();
    $sql='';
    switch($tipo){
        case "Segreteria":
            $sql="SELECT nome_utente FROM \"Unidb\".segreteria WHERE nome_utente = $1 AND password= $2";
            break;
        
        case "Docente":
            $sql="SELECT nome_utente FROM \"Unidb\".docente WHERE nome_utente= $1 AND password= $2";
            break;
            
        case "Studente":
            $sql="SELECT nome_utente FROM \"Unidb\".studente WHERE nome_utente = $1 AND password = $2";
            break;
    }
    $parametri = Array(
    	$utente,
    	$password
    );   
    
    $result = pg_prepare($db,"controllo_utente", $sql);
    $result = pg_execute($db,"controllo_utente", $parametri);
    if($row = pg_fetch_assoc($result)){
    	$loggato = $row['nome_utente'];
    }
    chiusura_connessione($db);
    return $loggato;
}

/*
Chiudo la connessione con il server PostgreSQL
*/
function chiusura_connessione($db) {
    return pg_close($db);
}
?>