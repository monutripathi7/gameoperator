<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-light">
    <div class="position-sticky pt-3 sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>" aria-current="page" href="<?php echo BASE_URL; ?>dashboard.php">
                    <i class="bi bi-house-door me-2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'players.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>players.php">
                    <i class="bi bi-people me-2"></i>
                    Players
                </a>
            </li>
			<li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'games.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>games.php">
                    <i class="bi bi-joystick me-2"></i>  <!-- Or bi-controller, bi-dice-5, etc. -->
                    Games
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-gear me-2"></i>
                    Settings
                </a>
            </li>
            <!-- Add more links as needed -->
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
            <span>Reports</span>
            <a class="link-secondary" href="#" aria-label="Add a new report">
                <i class="bi bi-plus-circle"></i>
            </a>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Current month
                </a>
            </li>
            <!-- Add more report links -->
        </ul>

         <hr>
         <div class="px-3">
              <a class="nav-link text-danger" href="<?php echo BASE_URL; ?>logout.php">
                 <i class="bi bi-box-arrow-right me-2"></i>
                 Logout
             </a>
         </div>

    </div>
</nav>