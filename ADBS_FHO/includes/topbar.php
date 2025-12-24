<div class="topbar">
    <div class="d-flex justify-content-between align-items-center w-100">
        <div class="d-flex align-items-center">
            <button class="btn btn-link text-dark d-md-none me-3" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <h5 class="mb-0 me-3 text-muted">
                <?php 
                if (isset($page_title)) {
                    echo $page_title;
                } else {
                    echo 'HR Management System';
                }
                ?>
            </h5>
        </div>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                </div>
                <div class="d-flex flex-column">
                    <strong><?php echo htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'] ?? 'User'); ?></strong>
                    <small class="text-muted" style="font-size: 0.8em;"><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></small>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end text-small shadow" aria-labelledby="dropdownUser1">
                <li><a class="dropdown-item" href="<?php echo isset($is_root) && $is_root ? '' : '../'; ?>logout.php">Sign out</a></li>
            </ul>
        </div>
    </div>
</div>
