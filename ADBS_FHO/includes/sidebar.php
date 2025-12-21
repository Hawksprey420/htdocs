<?php
// Determine paths based on location
$root_path = isset($is_root) && $is_root ? '' : '../';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <a href="<?php echo $root_path; ?>index.php" class="sidebar-brand">
        HR System
    </a>
    <div class="sidebar-nav">
        <a href="<?php echo $root_path; ?>index.php" class="sidebar-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="<?php echo $root_path; ?>views/employee-list.php" class="sidebar-link <?php echo strpos($current_page, 'employee') !== false ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Employees
        </a>
        <?php if (Auth::hasRole(1)): ?>
        <a href="<?php echo $root_path; ?>views/user-list.php" class="sidebar-link <?php echo strpos($current_page, 'user') !== false ? 'active' : ''; ?>">
            <i class="fas fa-user-shield"></i> Users
        </a>
        <?php endif; ?>
        <a href="<?php echo $root_path; ?>views/system-report.php" class="sidebar-link <?php echo $current_page == 'system-report.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i> Reports
        </a>
        <?php if (Auth::hasRole(1)): ?>
        <a href="<?php echo $root_path; ?>views/activity-log.php" class="sidebar-link <?php echo $current_page == 'activity-log.php' ? 'active' : ''; ?>">
            <i class="fas fa-history"></i> Activity Logs
        </a>
        <?php endif; ?>
    </div>
</div>
