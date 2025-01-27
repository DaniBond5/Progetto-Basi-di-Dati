<div class="container">
    <h2 style="text-align:center;" class="mb-4">Dashboard Segreteria</h2>
        <div class="dashboard-container border p-3">
            <div class="row">
                <div class="col-sm">
                    <div class="dashboard-item">
                    <div class="dashboard-item">
                        <h4 class="mb-3">Visualizzazione Carriera Completa Studente Attivo</h4>
                        <form action="carriera_segreteria.php" method="POST">
                        <input type="text" class="form-control mb-3" name="matricola" placeholder="Matricola Studente...">
                        <button type="submit" class="btn btn-primary">Visualizza</button>
                        </form>
                        </div>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4 class="mb-3">Visualizzazione Carriera Valida Studente Attivo</h4>
                        <form action="carriera_valida_segreteria.php" method="POST">
                        <input type="text" class="form-control mb-3" name="matricola" placeholder="Matricola Studente...">
                        <button type="submit" class="btn btn-primary">Visualizza</button>
                        </form>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4 class="mb-3">Visualizzazione Carriera Completa Studente Archiviato</h4>
                        <form action="carriera_studente_archiviato.php" method="POST">
                            <input type="text" class="form-control mb-3" name="matricola" placeholder="Matricola Studente...">
                            <button type="submit" class="btn btn-primary">Visualizza</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-sm">
                     <div class="dashboard-item">
                        <h4 class="mb-3">Visualizzazione Carriera Valida Studente Archiviato</h4>
                        <form action="carriera_valida_studente_storico.php" method="POST">
                        <input type="text" class="form-control mb-3" name="matricola" placeholder="Matricola Studente...">
                        <button type="submit" class="btn btn-primary">Visualizza</button>
                        </form>
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
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4 class="mb-3">Visualizzazione Corsi</h4>
                        <a class="btn btn-primary" href="mostracorsi.php" role="button">Visualizza</a>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4>Visualizzazione Studenti Attivi</h4>
                        <a class="btn btn-primary" href="mostra_studenti.php" role="button">Visualizza</a>
                    </div>
                </div>
                <div class="col-sm">
                    <div class=dashboard-item>
                        <h4>Visualizzazione Studenti Archiviati</h4>
                        <a class="btn btn-primary" href="mostra_storico_studenti.php" role="button">Visualizza</a>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4>Gestione Insegnamenti</h4>
                        <a class="btn btn-primary" href="gestione_insegnamenti.php" role="button">Gestisci</a>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4>Gestione Studenti</h4>
                        <a class="btn btn-primary" href="gestione_studenti.php" role="button">Gestisci</a>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4>Gestione Docenti</h4>
                        <a class="btn btn-primary" href="gestione_docenti.php" role="button">Gestisci</a>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4>Gestione Corsi</h4>
                        <a class="btn btn-primary" href="gestione_corsi.php" role="button">Gestisci</a>
                    </div>
                </div>
            </div>

            <div class="row mt-5 mb-3">
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4>Visualizzazione Docenti</h4>
                        <a class="btn btn-primary" href="mostra_docenti.php" role="button">Visualizza</a>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="dashboard-item">
                        <h4>Visualizzazione Insegnamenti</h4>
                        <a class="btn btn-primary" href="mostra_insegnamenti.php" role="button">Visualizza</a>
                    </div>
                </div>
      </div>  
</div>