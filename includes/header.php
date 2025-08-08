<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - <?php echo SYSTEM_NAME; ?></title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
    
    <style>
        /* ==========================================================================
           RESPONSIVE ADMIN PANEL - MOBILE FIRST APPROACH
           ========================================================================== */
        
        /* CSS CUSTOM PROPERTIES - Design System */
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --dark-color: #343a40;
            --light-color: #f8f9fa;
            
            /* Responsive Spacing System */
            --spacing-xs: 0.25rem;   /* 4px */
            --spacing-sm: 0.5rem;    /* 8px */
            --spacing-md: 1rem;      /* 16px */
            --spacing-lg: 1.5rem;    /* 24px */
            --spacing-xl: 2rem;      /* 32px */
            --spacing-xxl: 3rem;     /* 48px */
            
            /* Typography Scale */
            --font-size-xs: 0.75rem;   /* 12px */
            --font-size-sm: 0.875rem;  /* 14px */
            --font-size-base: 1rem;    /* 16px */
            --font-size-lg: 1.125rem;  /* 18px */
            --font-size-xl: 1.25rem;   /* 20px */
            --font-size-xxl: 1.5rem;   /* 24px */
            
            /* Layout Variables */
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 70px;
            --header-height: 60px;
            --border-radius: 12px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* ==========================================================================
           BASE STYLES - MOBILE FIRST
           ========================================================================== */
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Roboto', sans-serif;
            background-color: var(--light-color);
            font-size: var(--font-size-base);
            line-height: 1.6;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        /* ==========================================================================
           RESPONSIVE TYPOGRAPHY
           ========================================================================== */
        
        h1, .h1 { font-size: clamp(1.5rem, 4vw, 2.5rem); }
        h2, .h2 { font-size: clamp(1.25rem, 3.5vw, 2rem); }
        h3, .h3 { font-size: clamp(1.125rem, 3vw, 1.75rem); }
        h4, .h4 { font-size: clamp(1rem, 2.5vw, 1.5rem); }
        h5, .h5 { font-size: clamp(0.875rem, 2vw, 1.25rem); }
        h6, .h6 { font-size: clamp(0.75rem, 1.5vw, 1rem); }
        
        /* ==========================================================================
           SIDEBAR - MOBILE FIRST DESIGN
           ========================================================================== */
        
        .sidebar {
            position: fixed;
            top: 0;
            left: -100%;
            width: 85vw;
            max-width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            z-index: 1050;
            transition: var(--transition);
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .sidebar.show {
            left: 0;
        }
        
        .sidebar-header {
            padding: var(--spacing-lg);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .sidebar-header h5 {
            color: white;
            margin: 0;
            font-weight: 600;
            font-size: var(--font-size-lg);
        }
        
        .sidebar-nav {
            padding: var(--spacing-md) 0;
        }
        
        .sidebar-nav .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: var(--spacing-md) var(--spacing-lg);
            margin: var(--spacing-xs) var(--spacing-md);
            border-radius: var(--border-radius);
            transition: var(--transition);
            display: flex;
            align-items: center;
            font-size: var(--font-size-base);
            text-decoration: none;
            min-height: 48px; /* Touch-friendly */
        }
        
        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(4px);
        }
        
        .sidebar-nav .nav-link i {
            width: 20px;
            margin-right: var(--spacing-md);
            text-align: center;
        }
        
        /* Sidebar Overlay */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }
        
        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        /* ==========================================================================
           MAIN CONTENT AREA
           ========================================================================== */
        
        .main-content {
            min-height: 100vh;
            transition: var(--transition);
            padding-top: var(--header-height);
        }
        
        /* ==========================================================================
           TOP NAVBAR - RESPONSIVE
           ========================================================================== */
        
        .top-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: white;
            box-shadow: var(--box-shadow);
            z-index: 1030;
            display: flex;
            align-items: center;
            padding: 0 var(--spacing-md);
        }
        
        .navbar-toggle {
            background: none;
            border: none;
            font-size: var(--font-size-xl);
            color: var(--dark-color);
            padding: var(--spacing-sm);
            margin-right: var(--spacing-md);
            border-radius: var(--border-radius);
            min-width: 44px;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        
        .navbar-toggle:hover {
            background: var(--light-color);
        }
        
        .navbar-brand {
            font-weight: 600;
            color: var(--dark-color);
            text-decoration: none;
            margin-right: auto;
        }
        
        .navbar-user {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .user-dropdown {
            position: relative;
        }
        
        .user-toggle {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--border-radius);
            transition: var(--transition);
            min-height: 44px;
        }
        
        .user-toggle:hover {
            background: var(--light-color);
        }
        
        /* ==========================================================================
           RESPONSIVE CARDS
           ========================================================================== */
        
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            margin-bottom: var(--spacing-lg);
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
            border: none;
            padding: var(--spacing-lg);
        }
        
        .card-body {
            padding: var(--spacing-lg);
        }
        
        /* Stats Cards */
        .stats-card {
            background: white;
            border-radius: var(--border-radius);
            padding: var(--spacing-lg);
            text-align: center;
            transition: var(--transition);
            box-shadow: var(--box-shadow);
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--spacing-md);
            font-size: var(--font-size-xl);
            color: white;
        }
        
        /* ==========================================================================
           RESPONSIVE TABLES
           ========================================================================== */
        
        .table-responsive {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }
        
        .table {
            margin-bottom: 0;
            font-size: var(--font-size-sm);
        }
        
        .table thead th {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            font-weight: 600;
            padding: var(--spacing-md);
            font-size: var(--font-size-sm);
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: var(--spacing-md);
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }
        
        .table tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }
        
        /* ==========================================================================
           RESPONSIVE FORMS
           ========================================================================== */
        
        .form-control,
        .form-select {
            border-radius: var(--border-radius);
            border: 2px solid #e9ecef;
            padding: var(--spacing-md);
            font-size: var(--font-size-base);
            transition: var(--transition);
            min-height: 48px; /* Touch-friendly */
        }
        
        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: var(--spacing-sm);
            color: var(--dark-color);
        }
        
        /* ==========================================================================
           RESPONSIVE BUTTONS
           ========================================================================== */
        
        .btn {
            border-radius: var(--border-radius);
            padding: var(--spacing-md) var(--spacing-lg);
            font-weight: 600;
            transition: var(--transition);
            min-height: 44px; /* Touch-friendly */
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-sm {
            padding: var(--spacing-sm) var(--spacing-md);
            font-size: var(--font-size-sm);
            min-height: 36px;
        }
        
        .btn-group .btn {
            margin: 0;
        }
        
        /* ==========================================================================
           RESPONSIVE BADGES
           ========================================================================== */
        
        .badge {
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: 20px;
            font-weight: 500;
            font-size: var(--font-size-xs);
        }
        
        /* ==========================================================================
           RESPONSIVE MODALS
           ========================================================================== */
        
        .modal-content {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            border: none;
            padding: var(--spacing-lg);
        }
        
        .modal-body {
            padding: var(--spacing-lg);
        }
        
        .modal-footer {
            padding: var(--spacing-lg);
            border: none;
        }
        
        /* ==========================================================================
           RESPONSIVE PAGINATION
           ========================================================================== */
        
        .pagination {
            gap: var(--spacing-xs);
        }
        
        .page-link {
            border-radius: var(--border-radius);
            border: 1px solid #dee2e6;
            padding: var(--spacing-sm) var(--spacing-md);
            min-width: 44px;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* ==========================================================================
           RESPONSIVE UTILITIES
           ========================================================================== */
        
        .avatar-sm { width: 32px; height: 32px; }
        .avatar-md { width: 48px; height: 48px; }
        .avatar-lg { width: 64px; height: 64px; }
        .avatar-xl { width: 80px; height: 80px; }
        
        /* ==========================================================================
           TABLET STYLES (768px and up)
           ========================================================================== */
        
        @media (min-width: 768px) {
            .sidebar {
                width: var(--sidebar-width);
                left: -var(--sidebar-width);
            }
            
            .main-content {
                padding: var(--header-height) var(--spacing-lg) var(--spacing-lg);
            }
            
            .card-body {
                padding: var(--spacing-xl);
            }
            
            .table {
                font-size: var(--font-size-base);
            }
            
            .stats-icon {
                width: 70px;
                height: 70px;
                font-size: var(--font-size-xxl);
            }
            
            /* Modal adjustments for tablets */
            .modal-dialog {
                margin: var(--spacing-lg);
            }
        }
        
        /* ==========================================================================
           DESKTOP STYLES (1024px and up)
           ========================================================================== */
        
        @media (min-width: 1024px) {
            .sidebar {
                position: fixed;
                left: 0;
                width: var(--sidebar-width);
                transform: none;
            }
            
            .sidebar.collapsed {
                width: var(--sidebar-collapsed-width);
            }
            
            .sidebar.collapsed .sidebar-text {
                display: none;
            }
            
            .sidebar.collapsed .sidebar-header h5 {
                font-size: var(--font-size-lg);
            }
            
            .main-content {
                margin-left: var(--sidebar-width);
                padding: var(--header-height) var(--spacing-xl) var(--spacing-xl);
            }
            
            .main-content.expanded {
                margin-left: var(--sidebar-collapsed-width);
            }
            
            .top-navbar {
                left: var(--sidebar-width);
            }
            
            .top-navbar.expanded {
                left: var(--sidebar-collapsed-width);
            }
            
            .navbar-toggle {
                display: block;
            }
            
            /* Hide overlay on desktop */
            .sidebar-overlay {
                display: none;
            }
            
            /* Desktop table improvements */
            .table thead th {
                font-size: var(--font-size-base);
            }
            
            .table tbody td {
                padding: var(--spacing-lg);
            }
        }
        
        /* ==========================================================================
           LARGE DESKTOP STYLES (1366px and up)
           ========================================================================== */
        
        @media (min-width: 1366px) {
            .main-content {
                padding: var(--header-height) var(--spacing-xxl) var(--spacing-xxl);
            }
            
            .stats-icon {
                width: 80px;
                height: 80px;
            }
            
            .card-body {
                padding: var(--spacing-xxl);
            }
        }
        
        /* ==========================================================================
           EXTRA LARGE DESKTOP STYLES (1920px and up)
           ========================================================================== */
        
        @media (min-width: 1920px) {
            :root {
                --sidebar-width: 320px;
                --sidebar-collapsed-width: 80px;
            }
            
            .sidebar-nav .nav-link {
                padding: var(--spacing-lg) var(--spacing-xl);
                font-size: var(--font-size-lg);
            }
            
            .stats-icon {
                width: 100px;
                height: 100px;
            }
        }
        
        /* ==========================================================================
           PRINT STYLES
           ========================================================================== */
        
        @media print {
            .sidebar,
            .top-navbar,
            .btn,
            .sidebar-overlay {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            
            .card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
        
        /* ==========================================================================
           ACCESSIBILITY IMPROVEMENTS
           ========================================================================== */
        
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        /* Focus styles for keyboard navigation */
        .btn:focus,
        .form-control:focus,
        .nav-link:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }
        
        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .card {
                border: 2px solid var(--dark-color);
            }
            
            .btn-primary {
                background: var(--dark-color);
                border: 2px solid var(--dark-color);
            }
        }
        
        /* ==========================================================================
           RESPONSIVE CHART CONTAINERS
           ========================================================================== */
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        @media (min-width: 768px) {
            .chart-container {
                height: 400px;
            }
        }
        
        @media (min-width: 1024px) {
            .chart-container {
                height: 450px;
            }
        }
        
        /* ==========================================================================
           RESPONSIVE GRID SYSTEM ENHANCEMENTS
           ========================================================================== */
        
        .responsive-grid {
            display: grid;
            gap: var(--spacing-lg);
            grid-template-columns: 1fr;
        }
        
        @media (min-width: 768px) {
            .responsive-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 1024px) {
            .responsive-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (min-width: 1366px) {
            .responsive-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h5>
                <i class="fas fa-shield-alt me-2"></i>
                <span class="sidebar-text">Admin Panel</span>
            </h5>
        </div>
        
        <ul class="nav flex-column sidebar-nav">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" 
                   href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" 
                   href="users.php">
                    <i class="fas fa-users"></i>
                    <span class="sidebar-text">Usuários</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'deposits.php' ? 'active' : ''; ?>" 
                   href="deposits.php">
                    <i class="fas fa-arrow-down"></i>
                    <span class="sidebar-text">Depósitos</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'withdrawals.php' ? 'active' : ''; ?>" 
                   href="withdrawals.php">
                    <i class="fas fa-arrow-up"></i>
                    <span class="sidebar-text">Saques</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" 
                   href="products.php">
                    <i class="fas fa-box"></i>
                    <span class="sidebar-text">Produtos</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'affiliates.php' ? 'active' : ''; ?>" 
                   href="affiliates.php">
                    <i class="fas fa-network-wired"></i>
                    <span class="sidebar-text">Afiliados</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" 
                   href="reports.php">
                    <i class="fas fa-chart-bar"></i>
                    <span class="sidebar-text">Relatórios</span>
                </a>
            </li>
            
            <li class="nav-item mt-auto">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="sidebar-text">Sair</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Top Navbar -->
    <nav class="top-navbar" id="topNavbar">
        <button class="navbar-toggle" id="sidebarToggle" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>
        
        <a href="dashboard.php" class="navbar-brand">
            <span class="d-none d-md-inline"><?php echo SYSTEM_NAME; ?></span>
            <span class="d-md-none">Admin</span>
        </a>
        
        <div class="navbar-user">
            <div class="user-dropdown">
                <button class="user-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle"></i>
                    <span class="d-none d-sm-inline"><?php echo escape($_SESSION['admin_name']); ?></span>
                    <i class="fas fa-chevron-down d-none d-sm-inline"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php">
                        <i class="fas fa-user me-2"></i>Perfil
                    </a></li>
                    <li><a class="dropdown-item" href="settings.php">
                        <i class="fas fa-cog me-2"></i>Configurações
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Sair
                    </a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="container-fluid">
            <?php showNotification(); ?>