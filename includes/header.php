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
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --dark-color: #343a40;
            --light-color: #f8f9fa;
        }
        
        body {
            background-color: #f5f6fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1050;
            transition: all 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        /* Desktop: Sidebar colapsado */
        .sidebar.collapsed {
            width: 70px;
        }
        
        .sidebar.collapsed .sidebar-text {
            display: none;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .main-content {
            margin-left: 250px;
            transition: all 0.3s;
            min-height: 100vh;
        }
        
        .main-content.expanded {
            margin-left: 70px;
        }
        
        /* Overlay para mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
            display: none;
        }
        
        .sidebar-overlay.show {
            display: block;
        }
        
        /* Container responsivo */
        .container-fluid {
            padding-left: 15px;
            padding-right: 15px;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-bottom: 1px solid #e9ecef;
            position: sticky;
            top: 0;
            z-index: 1030;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
            margin-bottom: 1.5rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
            font-size: 0.9rem;
        }
        
        .table thead th {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            font-weight: 600;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .card-header-tabs .nav-link {
            color: white !important;
        }
        
        .card-header-tabs .nav-link.active {
            color: var(--primary-color) !important;
        }
        
        /* Responsividade para tabelas */
        .table-responsive {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* Stats cards responsivos */
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
        }
        
        /* Formulários responsivos */
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
            font-size: 0.95rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        /* Botões responsivos */
        .btn {
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }
        
        .btn-lg {
            padding: 12px 24px;
            font-size: 1.1rem;
        }
        
        /* Badges responsivos */
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.8rem;
        }
        
        /* Modais responsivos */
        .modal-content {
            border-radius: 15px;
            border: none;
            margin: 1rem;
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 1rem 1.5rem;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e9ecef;
        }
        
        /* Breadcrumbs responsivos */
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 1rem;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            content: ">";
            color: #6c757d;
        }
        
        /* Paginação responsiva */
        .pagination {
            margin-bottom: 0;
        }
        
        .page-link {
            border-radius: 8px;
            margin: 0 2px;
            border: 1px solid #dee2e6;
        }
        
        .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-color: var(--primary-color);
        }
        
        /* Alertas responsivos */
        .alert {
            border-radius: 10px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        /* Tooltips e popovers */
        .tooltip-inner {
            border-radius: 6px;
            font-size: 0.875rem;
        }
        
        /* === MEDIA QUERIES RESPONSIVAS === */
        
        /* Extra Large Devices (1400px and up) */
        @media (min-width: 1400px) {
            .container-fluid {
                max-width: 1320px;
                margin: 0 auto;
            }
            
            .stats-card {
                padding: 2rem;
            }
            
            .card-body {
                padding: 2rem;
            }
        }
        
        /* Large Devices (1200px and down) */
        @media (max-width: 1199.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .main-content.expanded {
                margin-left: 0;
            }
            
            .container-fluid {
                padding-left: 20px;
                padding-right: 20px;
            }
            
            .stats-card {
                margin-bottom: 1rem;
            }
            
            .card-header {
                padding: 1rem 1.25rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
        }
        
        /* Medium Devices (992px and down) */
        @media (max-width: 991.98px) {
            .table {
                font-size: 0.85rem;
            }
            
            .btn-group .btn {
                padding: 4px 8px;
                font-size: 0.8rem;
            }
            
            .stats-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }
            
            .modal-dialog {
                margin: 0.5rem;
            }
            
            .modal-content {
                margin: 0;
            }
            
            /* Esconder colunas menos importantes em tablets */
            .table .d-none.d-lg-table-cell {
                display: none !important;
            }
        }
        
        /* Small Devices (768px and down) */
        @media (max-width: 767.98px) {
            .sidebar {
                width: 280px;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .container-fluid {
                padding-left: 15px;
                padding-right: 15px;
            }
            
            .navbar {
                padding: 0.5rem 1rem;
            }
            
            .card {
                margin-bottom: 1rem;
            }
            
            .card-header {
                padding: 0.75rem 1rem;
                font-size: 0.95rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .table {
                font-size: 0.8rem;
            }
            
            .table th,
            .table td {
                padding: 0.5rem 0.25rem;
            }
            
            .btn {
                padding: 6px 12px;
                font-size: 0.875rem;
            }
            
            .btn-sm {
                padding: 4px 8px;
                font-size: 0.8rem;
            }
            
            .btn-group .btn {
                padding: 3px 6px;
                font-size: 0.75rem;
            }
            
            .stats-card {
                padding: 1rem;
                margin-bottom: 1rem;
            }
            
            .stats-card h3,
            .stats-card h4,
            .stats-card h5 {
                font-size: 1.25rem;
            }
            
            .stats-icon {
                width: 45px;
                height: 45px;
                font-size: 1.1rem;
                margin-bottom: 0.75rem;
            }
            
            .modal-dialog {
                margin: 0.25rem;
            }
            
            .modal-header {
                padding: 0.75rem 1rem;
            }
            
            .modal-body {
                padding: 1rem;
            }
            
            .modal-footer {
                padding: 0.75rem 1rem;
            }
            
            .form-control,
            .form-select {
                padding: 8px 12px;
                font-size: 0.9rem;
            }
            
            .breadcrumb {
                font-size: 0.875rem;
            }
            
            /* Esconder colunas em mobile */
            .table .d-none.d-md-table-cell {
                display: none !important;
            }
            
            /* Paginação compacta */
            .pagination {
                justify-content: center;
            }
            
            .page-link {
                padding: 0.375rem 0.75rem;
                font-size: 0.875rem;
            }
            
            /* Filtros em mobile */
            .row.g-3 > .col-md-3,
            .row.g-3 > .col-md-2 {
                margin-bottom: 0.75rem;
            }
        }
        
        /* Extra Small Devices (576px and down) */
        @media (max-width: 575.98px) {
            .sidebar {
                width: 85vw;
            }
            
            .sidebar-text {
                font-size: 0.9rem;
            }
            
            .container-fluid {
                padding-left: 10px;
                padding-right: 10px;
            }
            
            .navbar {
                padding: 0.5rem 0.75rem;
            }
            
            .navbar-brand {
                font-size: 1rem;
            }
            
            .card {
                border-radius: 10px;
                margin-bottom: 0.75rem;
            }
            
            .card-header {
                padding: 0.5rem 0.75rem;
                font-size: 0.9rem;
            }
            
            .card-body {
                padding: 0.75rem;
            }
            
            .table {
                font-size: 0.75rem;
            }
            
            .table th,
            .table td {
                padding: 0.375rem 0.125rem;
            }
            
            .btn {
                padding: 5px 10px;
                font-size: 0.8rem;
            }
            
            .btn-sm {
                padding: 3px 6px;
                font-size: 0.75rem;
            }
            
            .btn-group .btn {
                padding: 2px 4px;
                font-size: 0.7rem;
            }
            
            .stats-card {
                padding: 0.75rem;
                margin-bottom: 0.75rem;
            }
            
            .stats-card h3,
            .stats-card h4,
            .stats-card h5 {
                font-size: 1.1rem;
            }
            
            .stats-card p {
                font-size: 0.85rem;
                margin-bottom: 0.25rem;
            }
            
            .stats-card small {
                font-size: 0.75rem;
            }
            
            .stats-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
                margin-bottom: 0.5rem;
            }
            
            .modal-dialog {
                margin: 0.125rem;
            }
            
            .modal-header {
                padding: 0.5rem 0.75rem;
            }
            
            .modal-body {
                padding: 0.75rem;
            }
            
            .modal-footer {
                padding: 0.5rem 0.75rem;
            }
            
            .form-control,
            .form-select {
                padding: 6px 10px;
                font-size: 0.85rem;
            }
            
            .form-label {
                font-size: 0.875rem;
                margin-bottom: 0.25rem;
            }
            
            .breadcrumb {
                font-size: 0.8rem;
            }
            
            .alert {
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
            }
            
            /* Paginação muito compacta */
            .pagination {
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .page-link {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
                margin: 1px;
            }
            
            /* Esconder elementos não essenciais */
            .d-none.d-sm-inline,
            .d-none.d-sm-block {
                display: none !important;
            }
            
            /* Filtros em tela muito pequena */
            .row.g-3 > div {
                margin-bottom: 0.5rem;
            }
            
            /* Títulos menores */
            h1.h3 {
                font-size: 1.5rem;
            }
            
            h2, h3, h4, h5 {
                font-size: 1.25rem;
            }
        }
        
        /* Ultra Small Devices (400px and down) */
        @media (max-width: 399.98px) {
            .sidebar {
                width: 90vw;
            }
            
            .sidebar-text {
                font-size: 0.85rem;
            }
            
            .container-fluid {
                padding-left: 8px;
                padding-right: 8px;
            }
            
            .card {
                border-radius: 8px;
            }
            
            .card-header {
                padding: 0.375rem 0.5rem;
                font-size: 0.85rem;
            }
            
            .card-body {
                padding: 0.5rem;
            }
            
            .table {
                font-size: 0.7rem;
            }
            
            .btn {
                padding: 4px 8px;
                font-size: 0.75rem;
            }
            
            .btn-sm {
                padding: 2px 4px;
                font-size: 0.7rem;
            }
            
            .stats-card {
                padding: 0.5rem;
            }
            
            .stats-card h3,
            .stats-card h4,
            .stats-card h5 {
                font-size: 1rem;
            }
            
            .stats-icon {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
            }
            
            .form-control,
            .form-select {
                padding: 5px 8px;
                font-size: 0.8rem;
            }
            
            .modal-header,
            .modal-body,
            .modal-footer {
                padding: 0.5rem;
            }
            
            /* Títulos ainda menores */
            h1.h3 {
                font-size: 1.25rem;
            }
            
            h2, h3, h4, h5 {
                font-size: 1.1rem;
            }
        }
        
        /* Utilitários responsivos adicionais */
        .text-truncate-mobile {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .scroll-horizontal {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Melhorias de acessibilidade */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .card {
                background-color: #2d3748;
                color: #e2e8f0;
            }
            
            .table {
                color: #e2e8f0;
            }
            
            .form-control,
            .form-select {
                background-color: #4a5568;
                border-color: #718096;
                color: #e2e8f0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="p-3 text-center border-bottom border-light">
            <h5 class="text-white mb-0">
                <i class="fas fa-shield-alt me-2"></i>
                <span class="sidebar-text">Admin Panel</span>
            </h5>
        </div>
        
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" 
                   href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" 
                   href="users.php">
                    <i class="fas fa-users me-2"></i>
                    <span class="sidebar-text">Usuários</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'deposits.php' ? 'active' : ''; ?>" 
                   href="deposits.php">
                    <i class="fas fa-arrow-down me-2"></i>
                    <span class="sidebar-text">Depósitos</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'withdrawals.php' ? 'active' : ''; ?>" 
                   href="withdrawals.php">
                    <i class="fas fa-arrow-up me-2"></i>
                    <span class="sidebar-text">Saques</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" 
                   href="products.php">
                    <i class="fas fa-box me-2"></i>
                    <span class="sidebar-text">Produtos</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'affiliates.php' ? 'active' : ''; ?>" 
                   href="affiliates.php">
                    <i class="fas fa-network-wired me-2"></i>
                    <span class="sidebar-text">Afiliados</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" 
                   href="reports.php">
                    <i class="fas fa-chart-bar me-2"></i>
                    <span class="sidebar-text">Relatórios</span>
                </a>
            </li>
            
            <li class="nav-item mt-auto">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    <span class="sidebar-text">Sair</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <button class="btn btn-link" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" 
                           id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-2"></i>
                            <?php echo escape($_SESSION['admin_name']); ?>
                        </a>
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
            </div>
        </nav>
        
        <!-- Page Content -->
        <div class="container-fluid p-4">
            <?php showNotification(); ?>