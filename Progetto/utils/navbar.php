<nav class="navbar navbar-expand-lg navbar-light bg-light mb-5">
        <a class="navbar-brand" href="#">Progetto Basi di Dati</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <div class="navbar-nav">
            <?php 
                if(isset($loggato)){
                    $logout_link=$_SERVER['PHP_SELF']."?log=canc";
                    ?>
            <a class="nav-item nav-link"><?php echo("Benvenuto/a $loggato - ");?></a>
            <a class="nav-item nav-link active" href="dashboard.php">Dashboard</a>
            <a class="nav-item nav-link active" href="gestione_utente.php">Gestione Utente</a>
            <a class="nav-item nav-link active" href="<?php echo($logout_link); ?>">Logout</a>
            <?php
                }
            ?>
        </div>
        </div>
    </nav>