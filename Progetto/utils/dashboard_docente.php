<div class="container">
        <div class="dashboard-container">
            <div class="row mt-5">
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4 class="mb-3">Visualizzazione Esami</h4>
                        <a class="btn btn-primary" href="esami_docente.php" role="button">Visualizza</a>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4 class="mb-3">Modifica Esami</h4>
                        <a class="btn btn-primary" href="modifica_esame.php" role="button">Procedi</a>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4 class="mb-3">Nuovo Esame</h4>
                        <a class="btn btn-primary" href="inserimento_esame.php" role="button">Crea</a>
                    </div>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4 class="mb-3">Visualizzazione Corsi</h4>
                        <a class="btn btn-primary" href="mostracorsi.php" role="button">Visualizza</a>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4 class="mb-3">Visualizzazione Informazioni di un corso</h4>
                        <form action="informazioni_corso.php" method="POST">
                            <input type="text" class="form-control mb-3" name="id_corso" placeholder="ID Corso...">
                            <button type="submit" class="btn btn-primary">Visualizza</button>
                        </form>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4 class="mb-3">Inserimento Voti</h4>
                        <form action=inserimento_voti.php method="POST">
                            <input type="text" class="form-control mb-3" name="data_esame" placeholder="Data Esame... (Y/M/D)">
                            <input type="text" class="form-control mb-3" name="id_insegnamento" placeholder="ID Insegnamento...">
                            <button type="submit" class="btn btn-primary">Procedi</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="row mt-5">
                <div class="dashboard-item">
                    <h4 class="mb-3">Visualizza I Tuoi Insegnamenti</h4>
                    <a class="btn btn-primary" href="insegnamenti_docente.php" role="button">Visualizza</a>
                </div>
            </div>
      </div>  
    </div>