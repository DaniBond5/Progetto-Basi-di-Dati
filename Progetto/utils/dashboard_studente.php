<div class="container">
        <div class="dashboard-container">
            <div class="row">
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4 class="mb-3">Carriera Completa</h4>
                        <a class="btn btn-primary" href="carriera_studente.php" role="button">Visualizza</a>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4>Carriera Valida</h4>
                        <a class="btn btn-primary" href="carriera_valida_studente.php" role="button">Visualizza</a>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4 class="mb-3">Iscrizione Esame</h4>
                        <a class="btn btn-primary" href="iscrizione_esame.php" role="button">Iscriviti</a>
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
                        <h4 class="mb-3">Le Tue Iscrizioni</h4>
                        <a class="btn btn-primary" href="iscrizioni_studente.php" role="button">Visualizza</a>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4 class="mb-3">Visualizzazione Informazioni su un corso</h4>
                        <form action="informazioni_corso.php" method="POST">
                        <input type="text" class="form-control mb-3" name="id_corso" placeholder="ID Corso...">
                        <button type="submit" class="btn btn-primary">Visualizza</button>
                        </form>
                    </div>
                </div>
            </div>
      </div>  
    </div>