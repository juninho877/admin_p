</div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        /* ==========================================================================
           RESPONSIVE ADMIN PANEL JAVASCRIPT
           ========================================================================== */
        
        class ResponsiveAdminPanel {
            constructor() {
                this.sidebar = document.getElementById('sidebar');
                this.sidebarToggle = document.getElementById('sidebarToggle');
                this.sidebarOverlay = document.getElementById('sidebarOverlay');
                this.mainContent = document.getElementById('mainContent');
                this.topNavbar = document.getElementById('topNavbar');
                this.body = document.body;
                
                this.breakpoints = {
                    mobile: 768,
                    tablet: 1024,
                    desktop: 1366,
                    large: 1920
                };
                
                this.init();
            }
            
            init() {
                this.setupEventListeners();
                this.handleResize();
                this.initializeComponents();
                
                // Set initial state based on screen size
                window.addEventListener('DOMContentLoaded', () => {
                    this.handleResize();
                });
            }
            
            setupEventListeners() {
                // Sidebar toggle
                this.sidebarToggle?.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.toggleSidebar();
                });
                
                // Overlay click to close sidebar
                this.sidebarOverlay?.addEventListener('click', () => {
                    this.closeSidebar();
                });
                
                // Window resize handler
                let resizeTimer;
                window.addEventListener('resize', () => {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(() => {
                        this.handleResize();
                    }, 150);
                });
                
                // Keyboard navigation
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && this.isMobileView()) {
                        this.closeSidebar();
                    }
                });
                
                // Touch events for mobile swipe
                this.setupTouchEvents();
            }
            
            setupTouchEvents() {
                let startX = 0;
                let currentX = 0;
                let isDragging = false;
                
                document.addEventListener('touchstart', (e) => {
                    if (this.isMobileView()) {
                        startX = e.touches[0].clientX;
                        isDragging = true;
                    }
                }, { passive: true });
                
                document.addEventListener('touchmove', (e) => {
                    if (!isDragging || !this.isMobileView()) return;
                    
                    currentX = e.touches[0].clientX;
                    const diffX = currentX - startX;
                    
                    // Swipe right from left edge to open sidebar
                    if (startX < 50 && diffX > 100 && !this.sidebar.classList.contains('show')) {
                        this.openSidebar();
                    }
                    
                    // Swipe left to close sidebar
                    if (this.sidebar.classList.contains('show') && diffX < -100) {
                        this.closeSidebar();
                    }
                }, { passive: true });
                
                document.addEventListener('touchend', () => {
                    isDragging = false;
                }, { passive: true });
            }
            
            toggleSidebar() {
                if (this.isMobileView()) {
                    // Mobile/Tablet behavior
                    if (this.sidebar.classList.contains('show')) {
                        this.closeSidebar();
                    } else {
                        this.openSidebar();
                    }
                } else {
                    // Desktop behavior
                    this.sidebar.classList.toggle('collapsed');
                    this.mainContent.classList.toggle('expanded');
                    this.topNavbar.classList.toggle('expanded');
                    
                    // Store preference
                    localStorage.setItem('sidebarCollapsed', this.sidebar.classList.contains('collapsed'));
                }
            }
            
            openSidebar() {
                this.sidebar.classList.add('show');
                this.sidebarOverlay.classList.add('show');
                this.body.style.overflow = 'hidden';
                
                // Focus management for accessibility
                const firstLink = this.sidebar.querySelector('.nav-link');
                if (firstLink) {
                    setTimeout(() => firstLink.focus(), 300);
                }
            }
            
            closeSidebar() {
                this.sidebar.classList.remove('show');
                this.sidebarOverlay.classList.remove('show');
                this.body.style.overflow = '';
                
                // Return focus to toggle button
                this.sidebarToggle?.focus();
            }
            
            handleResize() {
                const width = window.innerWidth;
                
                if (width >= this.breakpoints.tablet) {
                    // Desktop view
                    this.closeSidebar(); // Close mobile sidebar
                    this.sidebar.classList.remove('show');
                    this.sidebarOverlay.classList.remove('show');
                    this.body.style.overflow = '';
                    
                    // Restore desktop sidebar state
                    const wasCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                    if (wasCollapsed) {
                        this.sidebar.classList.add('collapsed');
                        this.mainContent.classList.add('expanded');
                        this.topNavbar.classList.add('expanded');
                    } else {
                        this.sidebar.classList.remove('collapsed');
                        this.mainContent.classList.remove('expanded');
                        this.topNavbar.classList.remove('expanded');
                    }
                } else {
                    // Mobile/Tablet view
                    this.sidebar.classList.remove('collapsed');
                    this.mainContent.classList.remove('expanded');
                    this.topNavbar.classList.remove('expanded');
                }
                
                // Update DataTables if present
                this.updateDataTables();
            }
            
            isMobileView() {
                return window.innerWidth < this.breakpoints.tablet;
            }
            
            initializeComponents() {
                this.initializeDataTables();
                this.initializeSelect2();
                this.initializeDateRangePicker();
                this.initializeTooltips();
                this.initializeCharts();
            }
            
            initializeDataTables() {
                // Responsive DataTables configuration
                if (typeof $.fn.DataTable !== 'undefined') {
                    $.extend(true, $.fn.dataTable.defaults, {
                        language: {
                            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
                        },
                        responsive: {
                            details: {
                                type: 'column',
                                target: 'tr'
                            }
                        },
                        pageLength: this.getPageLength(),
                        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
                        dom: this.getDataTablesDom(),
                        scrollX: true,
                        autoWidth: false,
                        columnDefs: [{
                            className: 'dtr-control',
                            orderable: false,
                            targets: 0
                        }]
                    });
                }
            }
            
            getPageLength() {
                const width = window.innerWidth;
                if (width < this.breakpoints.mobile) return 10;
                if (width < this.breakpoints.tablet) return 15;
                return 25;
            }
            
            getDataTablesDom() {
                const width = window.innerWidth;
                if (width < this.breakpoints.mobile) {
                    return '<"row"<"col-12"f>><"row"<"col-12"tr>><"row"<"col-12"p>>';
                }
                return '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                       '<"row"<"col-sm-12"tr>>' +
                       '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>';
            }
            
            updateDataTables() {
                if (typeof $.fn.DataTable !== 'undefined') {
                    $('.dataTable').each(function() {
                        const table = $(this).DataTable();
                        table.columns.adjust().responsive.recalc();
                    });
                }
            }
            
            initializeSelect2() {
                if (typeof $.fn.select2 !== 'undefined') {
                    $('.select2').select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        dropdownAutoWidth: true
                    });
                }
            }
            
            initializeDateRangePicker() {
                if (typeof $.fn.daterangepicker !== 'undefined') {
                    $('.daterange').daterangepicker({
                        locale: {
                            format: 'DD/MM/YYYY',
                            separator: ' - ',
                            applyLabel: 'Aplicar',
                            cancelLabel: 'Cancelar',
                            fromLabel: 'De',
                            toLabel: 'Até',
                            customRangeLabel: 'Personalizado',
                            weekLabel: 'S',
                            daysOfWeek: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
                            monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                                       'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                            firstDay: 0
                        },
                        ranges: {
                            'Hoje': [moment(), moment()],
                            'Ontem': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                            'Últimos 7 dias': [moment().subtract(6, 'days'), moment()],
                            'Últimos 30 dias': [moment().subtract(29, 'days'), moment()],
                            'Este mês': [moment().startOf('month'), moment().endOf('month')],
                            'Mês passado': [moment().subtract(1, 'month').startOf('month'), 
                                           moment().subtract(1, 'month').endOf('month')]
                        }
                    });
                }
            }
            
            initializeTooltips() {
                // Initialize Bootstrap tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl, {
                        trigger: window.innerWidth < 768 ? 'click' : 'hover'
                    });
                });
            }
            
            initializeCharts() {
                // Chart.js responsive configuration
                if (typeof Chart !== 'undefined') {
                    Chart.defaults.responsive = true;
                    Chart.defaults.maintainAspectRatio = false;
                    Chart.defaults.plugins.legend.position = window.innerWidth < 768 ? 'bottom' : 'top';
                }
            }
        }
        
        // Utility Functions
        function confirmDelete(url, message = 'Tem certeza que deseja excluir este item?') {
            Swal.fire({
                title: 'Confirmar exclusão',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar',
                customClass: {
                    popup: 'swal-responsive'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
        
        function showSuccess(message) {
            Swal.fire({
                title: 'Sucesso!',
                text: message,
                icon: 'success',
                timer: 3000,
                showConfirmButton: false,
                customClass: {
                    popup: 'swal-responsive'
                }
            });
        }
        
        function showError(message) {
            Swal.fire({
                title: 'Erro!',
                text: message,
                icon: 'error',
                customClass: {
                    popup: 'swal-responsive'
                }
            });
        }
        
        function formatMoney(value, currency = 'BRL') {
            if (currency === 'BRL') {
                return new Intl.NumberFormat('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                }).format(value);
            } else {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD'
                }).format(value);
            }
        }
        
        // Initialize the responsive admin panel
        document.addEventListener('DOMContentLoaded', function() {
            window.adminPanel = new ResponsiveAdminPanel();
            
            // Auto-refresh for pending items (mobile-friendly)
            if (window.location.pathname.includes('deposits.php') || 
                window.location.pathname.includes('withdrawals.php')) {
                
                const refreshInterval = window.innerWidth < 768 ? 60000 : 30000; // 60s mobile, 30s desktop
                
                setInterval(function() {
                    if (document.querySelector('.badge-warning')) {
                        location.reload();
                    }
                }, refreshInterval);
            }
        });
        
        // Additional responsive styles for SweetAlert2
        const style = document.createElement('style');
        style.textContent = `
            .swal-responsive {
                width: 90% !important;
                max-width: 500px !important;
                margin: 1rem !important;
            }
            
            @media (max-width: 576px) {
                .swal-responsive {
                    width: 95% !important;
                    margin: 0.5rem !important;
                }
                
                .swal2-title {
                    font-size: 1.25rem !important;
                }
                
                .swal2-content {
                    font-size: 0.875rem !important;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>