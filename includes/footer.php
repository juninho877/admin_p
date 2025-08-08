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
        // Sidebar Toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
        
        // DataTables Default Config
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
            },
            responsive: true,
            pageLength: getResponsivePageLength(),
            lengthMenu: getResponsiveLengthMenu(),
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            scrollX: true,
            autoWidth: false,
            columnDefs: [
                { responsivePriority: 1, targets: 0 },
                { responsivePriority: 2, targets: -1 }
            ]
        });
        
        // Função para determinar pageLength responsivo
        function getResponsivePageLength() {
            if (window.innerWidth <= 576) return 10;
            if (window.innerWidth <= 768) return 15;
            if (window.innerWidth <= 992) return 20;
            return 25;
        }
        
        // Função para determinar lengthMenu responsivo
        function getResponsiveLengthMenu() {
            if (window.innerWidth <= 576) {
                return [[5, 10, 25, -1], [5, 10, 25, "Todos"]];
            }
            if (window.innerWidth <= 768) {
                return [[10, 15, 25, 50, -1], [10, 15, 25, 50, "Todos"]];
            }
            return [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]];
        }
        
        // Select2 Default Config
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownAutoWidth: true,
            minimumResultsForSearch: window.innerWidth <= 768 ? 5 : 10
        });
        
        // Date Range Picker
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
        
        // Confirm Delete
        function confirmDelete(url, message = 'Tem certeza que deseja excluir este item?') {
            Swal.fire({
                title: 'Confirmar exclusão',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
        
        // Success/Error Messages
        function showSuccess(message) {
            Swal.fire({
                title: 'Sucesso!',
                text: message,
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
            });
        }
        
        function showError(message) {
            Swal.fire({
                title: 'Erro!',
                text: message,
                icon: 'error'
            });
        }
        
        // Format Money
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
        
        // Função para otimizar tabelas em mobile
        function optimizeTablesForMobile() {
            if (window.innerWidth <= 768) {
                $('.table').each(function() {
                    $(this).addClass('table-sm');
                });
                
                // Esconder colunas menos importantes
                $('.table th:nth-child(n+6), .table td:nth-child(n+6)').addClass('d-none d-lg-table-cell');
            } else {
                $('.table').removeClass('table-sm');
                $('.d-none.d-lg-table-cell').removeClass('d-none d-lg-table-cell');
            }
        }
        
        // Função para ajustar modais
        function adjustModalsForMobile() {
            $('.modal').each(function() {
                const modal = $(this);
                if (window.innerWidth <= 576) {
                    modal.find('.modal-dialog').addClass('modal-fullscreen-sm-down');
                } else {
                    modal.find('.modal-dialog').removeClass('modal-fullscreen-sm-down');
                }
            });
        }
        
        // Função para otimizar formulários
        function optimizeFormsForMobile() {
            if (window.innerWidth <= 768) {
                $('.row.g-3 > div').removeClass('col-md-3 col-md-2 col-md-4 col-md-6').addClass('col-12');
                $('.btn-group').addClass('d-grid gap-1');
            } else {
                // Restaurar classes originais seria complexo, então deixamos como está
            }
        }
        
        // Função para ajustar cards stats
        function adjustStatsCards() {
            if (window.innerWidth <= 576) {
                $('.stats-card h3, .stats-card h4, .stats-card h5').each(function() {
                    const text = $(this).text();
                    if (text.length > 10) {
                        $(this).attr('title', text).addClass('text-truncate-mobile');
                    }
                });
            }
        }
        
        // Função para otimizar paginação
        function optimizePagination() {
            $('.pagination').each(function() {
                const pagination = $(this);
                if (window.innerWidth <= 576) {
                    // Mostrar apenas algumas páginas em mobile
                    pagination.find('.page-item').each(function(index) {
                        if (index > 2 && index < pagination.find('.page-item').length - 3) {
                            if (!$(this).hasClass('active')) {
                                $(this).addClass('d-none d-sm-inline-block');
                            }
                        }
                    });
                } else {
                    pagination.find('.d-none.d-sm-inline-block').removeClass('d-none d-sm-inline-block');
                }
            });
        }
        
        // Função para adicionar scroll horizontal em tabelas
        function addHorizontalScroll() {
            $('.table-responsive').each(function() {
                if (!$(this).hasClass('scroll-horizontal')) {
                    $(this).addClass('scroll-horizontal');
                }
            });
        }
        
        // Função para otimizar tooltips em mobile
        function optimizeTooltips() {
            if (window.innerWidth <= 768) {
                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
            } else {
                // Reativar tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        }
        
        // Aplicar otimizações no carregamento
        $(document).ready(function() {
            optimizeTablesForMobile();
            adjustModalsForMobile();
            optimizeFormsForMobile();
            adjustStatsCards();
            optimizePagination();
            addHorizontalScroll();
            optimizeTooltips();
        });
        
        // Sidebar Toggle Logic
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            function handleResize() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                } else {
                    sidebar.classList.remove('collapsed');
                    mainContent.classList.remove('expanded');
                }
            }
            
            // Initial check
            handleResize();
            
            // Handle window resize
            window.addEventListener('resize', function() {
                handleResize();
                
                // Reaplica otimizações responsivas
                setTimeout(function() {
                    optimizeTablesForMobile();
                    adjustModalsForMobile();
                    adjustStatsCards();
                    optimizePagination();
                    optimizeTooltips();
                }, 100);
            });
            
            // Handle ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    // Close any open modals
                    $('.modal.show').modal('hide');
                    
                    // Close any open dropdowns
                    $('.dropdown-menu.show').removeClass('show');
                }
            });
        });
        
        // Função para detectar orientação em dispositivos móveis
        function handleOrientationChange() {
            setTimeout(function() {
                optimizeTablesForMobile();
                adjustModalsForMobile();
                adjustStatsCards();
            }, 500);
        }
        
        // Event listener para mudança de orientação
        window.addEventListener('orientationchange', handleOrientationChange);
        
        // Função para melhorar performance em dispositivos móveis
        function optimizePerformance() {
            if (window.innerWidth <= 768) {
                // Reduzir animações em mobile
                $('*').css({
                    'transition-duration': '0.2s',
                    'animation-duration': '0.2s'
                });
                
                // Lazy loading para imagens
                $('img').attr('loading', 'lazy');
            }
        }
        
        // Aplicar otimizações de performance
        $(document).ready(function() {
            optimizePerformance();
        });
        
        // Auto-refresh for pending items
        if (window.location.pathname.includes('deposits.php') || 
            window.location.pathname.includes('withdrawals.php')) {
            setInterval(function() {
                if (document.querySelector('.badge-warning')) {
                    location.reload();
                }
            }, 30000); // 30 seconds
        }
        
        // Tooltips (otimizado para mobile)
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                trigger: window.innerWidth <= 768 ? 'click' : 'hover'
            });
        });
        
        // Função para melhorar acessibilidade
        function improveAccessibility() {
            // Adicionar labels para screen readers
            $('button[data-bs-toggle="tooltip"]').each(function() {
                if (!$(this).attr('aria-label')) {
                    $(this).attr('aria-label', $(this).attr('title') || 'Botão');
                }
            });
            
            // Melhorar navegação por teclado
            $('a, button, input, select, textarea').attr('tabindex', function(index) {
                return index + 1;
            });
        }
        
        // Aplicar melhorias de acessibilidade
        $(document).ready(function() {
            improveAccessibility();
        });
    </script>
</body>
</html>