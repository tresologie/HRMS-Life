<ul class="navbar-nav sidebar sidebar-light accordion" id="accordionSidebar">
      <a class="sidebar-brand d-flex align-items-center bg-gradient-primary justify-content-center" href="index.php">
        <div class="sidebar-brand-icon" >
          <img src="img/logo/life.jpg">
        </div>
        <div class="sidebar-brand-text mx-3">HRMS-Life</div>
      </a>

      <div class="sidebar-heading">
      </br> Statistique
      </div>
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBootstrapschemes"
          aria-expanded="true" aria-controls="collapseBootstrapschemes">
          <i class="fas fa-fw fa-tachometer-alt" style="color: blue;"></i>
          <span>Tableau de bord</span>
        </a>
        <div id="collapseBootstrapschemes" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Gérer les statistiques</h6>
            <a class="collapse-item" href="index.php">Principales</a>
            <a class="collapse-item" href="indexPresence.php">Présences</a>
            <a class="collapse-item" href="indexSuppl.php">Supplémentaires</a>
          
          </div>
        </div>
      </li>
  
      <hr class="sidebar-divider">
      <div class="sidebar-heading">
        Appel
      </div>
      </li>
       <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBootstrap2"
          aria-expanded="true" aria-controls="collapseBootstrap2">
          <i class="fas fa-chart-bar" style="color: blue;"></i>
          <span>Gérer les employés</span>
        </a>
        <div id="collapseBootstrap2" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Gérer les employés</h6>
            <a class="collapse-item" href="createEmployees.php">Ajouter un employé</a>
            <a class="collapse-item" href="viewStudents.php">Voir les employés</a>
            <a class="collapse-item" href="viewStudentAttendance.php">Voir les présences</br>d'un employé</a>
            <a class="collapse-item" href="viewPresences.php">Présences de tous les</br> employés</a>
           
          </div>
        </div>
      </li>

      <hr class="sidebar-divider">
      <div class="sidebar-heading">
       Supplémentaires
      </div>
      </li>
       <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBootstrapcon"
          aria-expanded="true" aria-controls="collapseBootstrapcon">
          <i class="fas fa-clock fa-2x" style="color: blue;"></i>
          <span>Heures Supplémentaires</span>
        </a>
        <div id="collapseBootstrapcon" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Supplémentaires</h6>
            <a class="collapse-item" href="viewSuppSelect.php">Heures suppl(un employé)</a>
            <a class="collapse-item" href="viewSuppAll.php">Heures suppl(Tous)</a>
            <a class="collapse-item" href="historiSupp.php">Historique de paiement</a>
          </div>
        </div>
      </li>


      <hr class="sidebar-divider">
      <div class="sidebar-heading">
      Salaires
      </div>
      <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePage" aria-expanded="true"
          aria-controls="collapsePage">
          <i class="fas fa-money-bill fa-2x" style="color: blue;"></i>
          <span>Informations Banquaires</span>
        </a>
        <div id="collapsePage" class="collapse" aria-labelledby="headingPage" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Salaire</h6>
            <a class="collapse-item" href="addBankInfo.php">Ajouter les </br> info Bancaires </a>
            <a class="collapse-item" href="viewBankInfo.php">Voir les nfo Bancaires</a>
            <a class="collapse-item" href="salairePresences.php">Salaire des employés</a>
            <a class="collapse-item" href="historiSalaire.php">Historique de paiement</a>
           
          </div>
        </div>
      </li>
     
      </li>
      <hr class="sidebar-divider">
      <div class="version" id="version-ruangadmin"></div>
    </ul>