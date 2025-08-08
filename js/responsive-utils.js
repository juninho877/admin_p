/* ==========================================================================
   RESPONSIVE UTILITIES JAVASCRIPT
   ========================================================================== */

/**
 * Responsive Utility Class
 * Provides helper methods for responsive behavior
 */
class ResponsiveUtils {
    constructor() {
        this.breakpoints = {
            xs: 0,
            sm: 576,
            md: 768,
            lg: 1024,
            xl: 1366,
            xxl: 1920
        };
        
        this.currentBreakpoint = this.getCurrentBreakpoint();
        this.setupResizeListener();
    }
    
    /**
     * Get current breakpoint
     */
    getCurrentBreakpoint() {
        const width = window.innerWidth;
        
        if (width >= this.breakpoints.xxl) return 'xxl';
        if (width >= this.breakpoints.xl) return 'xl';
        if (width >= this.breakpoints.lg) return 'lg';
        if (width >= this.breakpoints.md) return 'md';
        if (width >= this.breakpoints.sm) return 'sm';
        return 'xs';
    }
    
    /**
     * Check if current screen is mobile
     */
    isMobile() {
        return this.currentBreakpoint === 'xs' || this.currentBreakpoint === 'sm';
    }
    
    /**
     * Check if current screen is tablet
     */
    isTablet() {
        return this.currentBreakpoint === 'md';
    }
    
    /**
     * Check if current screen is desktop
     */
    isDesktop() {
        return this.currentBreakpoint === 'lg' || this.currentBreakpoint === 'xl' || this.currentBreakpoint === 'xxl';
    }
    
    /**
     * Check if screen width matches breakpoint
     */
    matchesBreakpoint(breakpoint) {
        return this.currentBreakpoint === breakpoint;
    }
    
    /**
     * Check if screen width is at least the specified breakpoint
     */
    isAtLeast(breakpoint) {
        const currentWidth = window.innerWidth;
        return currentWidth >= this.breakpoints[breakpoint];
    }
    
    /**
     * Setup resize listener
     */
    setupResizeListener() {
        let resizeTimer;
        
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                const oldBreakpoint = this.currentBreakpoint;
                this.currentBreakpoint = this.getCurrentBreakpoint();
                
                if (oldBreakpoint !== this.currentBreakpoint) {
                    this.onBreakpointChange(oldBreakpoint, this.currentBreakpoint);
                }
            }, 150);
        });
    }
    
    /**
     * Callback for breakpoint changes
     */
    onBreakpointChange(oldBreakpoint, newBreakpoint) {
        // Dispatch custom event
        window.dispatchEvent(new CustomEvent('breakpointChange', {
            detail: { oldBreakpoint, newBreakpoint }
        }));
        
        // Update body class
        document.body.classList.remove(`breakpoint-${oldBreakpoint}`);
        document.body.classList.add(`breakpoint-${newBreakpoint}`);
    }
    
    /**
     * Get optimal page size for DataTables based on screen size
     */
    getOptimalPageSize() {
        if (this.isMobile()) return 10;
        if (this.isTablet()) return 15;
        return 25;
    }
    
    /**
     * Get optimal chart height based on screen size
     */
    getOptimalChartHeight() {
        if (this.isMobile()) return 250;
        if (this.isTablet()) return 350;
        return 400;
    }
    
    /**
     * Check if device supports touch
     */
    isTouchDevice() {
        return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    }
    
    /**
     * Get safe area insets for devices with notches
     */
    getSafeAreaInsets() {
        const style = getComputedStyle(document.documentElement);
        return {
            top: parseInt(style.getPropertyValue('--sat') || '0'),
            right: parseInt(style.getPropertyValue('--sar') || '0'),
            bottom: parseInt(style.getPropertyValue('--sab') || '0'),
            left: parseInt(style.getPropertyValue('--sal') || '0')
        };
    }
}

/**
 * Responsive Table Handler
 */
class ResponsiveTable {
    constructor(tableElement, options = {}) {
        this.table = tableElement;
        this.options = {
            stackedBreakpoint: 'md',
            scrollBreakpoint: 'sm',
            ...options
        };
        
        this.init();
    }
    
    init() {
        this.setupResponsiveBehavior();
        this.setupScrollIndicators();
    }
    
    setupResponsiveBehavior() {
        const utils = new ResponsiveUtils();
        
        const updateTableMode = () => {
            if (utils.isMobile()) {
                this.enableStackedMode();
            } else {
                this.enableScrollMode();
            }
        };
        
        updateTableMode();
        window.addEventListener('breakpointChange', updateTableMode);
    }
    
    enableStackedMode() {
        this.table.classList.add('table-stacked');
        this.table.classList.remove('table-scroll');
    }
    
    enableScrollMode() {
        this.table.classList.add('table-scroll');
        this.table.classList.remove('table-stacked');
    }
    
    setupScrollIndicators() {
        const wrapper = this.table.closest('.table-responsive');
        if (!wrapper) return;
        
        const updateScrollIndicators = () => {
            const canScrollLeft = wrapper.scrollLeft > 0;
            const canScrollRight = wrapper.scrollLeft < (wrapper.scrollWidth - wrapper.clientWidth);
            
            wrapper.classList.toggle('can-scroll-left', canScrollLeft);
            wrapper.classList.toggle('can-scroll-right', canScrollRight);
        };
        
        wrapper.addEventListener('scroll', updateScrollIndicators);
        window.addEventListener('resize', updateScrollIndicators);
        updateScrollIndicators();
    }
}

/**
 * Responsive Form Handler
 */
class ResponsiveForm {
    constructor(formElement, options = {}) {
        this.form = formElement;
        this.options = {
            stackedBreakpoint: 'md',
            ...options
        };
        
        this.init();
    }
    
    init() {
        this.setupResponsiveLayout();
        this.setupTouchOptimizations();
        this.setupValidationDisplay();
    }
    
    setupResponsiveLayout() {
        const utils = new ResponsiveUtils();
        
        const updateLayout = () => {
            if (utils.isMobile()) {
                this.enableStackedLayout();
            } else {
                this.enableGridLayout();
            }
        };
        
        updateLayout();
        window.addEventListener('breakpointChange', updateLayout);
    }
    
    enableStackedLayout() {
        this.form.classList.add('form-stacked');
        this.form.classList.remove('form-grid');
    }
    
    enableGridLayout() {
        this.form.classList.add('form-grid');
        this.form.classList.remove('form-stacked');
    }
    
    setupTouchOptimizations() {
        const utils = new ResponsiveUtils();
        
        if (utils.isTouchDevice()) {
            // Increase touch targets
            const inputs = this.form.querySelectorAll('input, select, textarea, button');
            inputs.forEach(input => {
                input.style.minHeight = '48px';
                input.style.minWidth = '48px';
            });
        }
    }
    
    setupValidationDisplay() {
        const utils = new ResponsiveUtils();
        
        const inputs = this.form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('invalid', (e) => {
                if (utils.isMobile()) {
                    // Show validation in a more mobile-friendly way
                    this.showMobileValidation(input, e.target.validationMessage);
                }
            });
        });
    }
    
    showMobileValidation(input, message) {
        // Remove existing validation
        const existingError = input.parentNode.querySelector('.mobile-validation-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Create mobile-friendly validation message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'mobile-validation-error alert alert-danger mt-2';
        errorDiv.textContent = message;
        
        input.parentNode.appendChild(errorDiv);
        
        // Remove after 5 seconds
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 5000);
    }
}

/**
 * Responsive Modal Handler
 */
class ResponsiveModal {
    constructor(modalElement, options = {}) {
        this.modal = modalElement;
        this.options = {
            fullscreenBreakpoint: 'sm',
            ...options
        };
        
        this.init();
    }
    
    init() {
        this.setupResponsiveBehavior();
        this.setupTouchGestures();
    }
    
    setupResponsiveBehavior() {
        const utils = new ResponsiveUtils();
        
        const updateModalMode = () => {
            if (utils.isMobile()) {
                this.enableFullscreenMode();
            } else {
                this.enableCenteredMode();
            }
        };
        
        updateModalMode();
        window.addEventListener('breakpointChange', updateModalMode);
    }
    
    enableFullscreenMode() {
        this.modal.classList.add('modal-fullscreen-sm-down');
    }
    
    enableCenteredMode() {
        this.modal.classList.remove('modal-fullscreen-sm-down');
    }
    
    setupTouchGestures() {
        const utils = new ResponsiveUtils();
        
        if (!utils.isTouchDevice()) return;
        
        let startY = 0;
        let currentY = 0;
        let isDragging = false;
        
        const modalDialog = this.modal.querySelector('.modal-dialog');
        
        modalDialog.addEventListener('touchstart', (e) => {
            startY = e.touches[0].clientY;
            isDragging = true;
        }, { passive: true });
        
        modalDialog.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            
            currentY = e.touches[0].clientY;
            const diffY = currentY - startY;
            
            // Allow swipe down to close on mobile
            if (diffY > 100 && utils.isMobile()) {
                const modalInstance = bootstrap.Modal.getInstance(this.modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }
        }, { passive: true });
        
        modalDialog.addEventListener('touchend', () => {
            isDragging = false;
        }, { passive: true });
    }
}

/**
 * Responsive Chart Handler
 */
class ResponsiveChart {
    constructor(chartElement, chartInstance, options = {}) {
        this.element = chartElement;
        this.chart = chartInstance;
        this.options = {
            mobileHeight: 250,
            tabletHeight: 350,
            desktopHeight: 400,
            ...options
        };
        
        this.init();
    }
    
    init() {
        this.setupResponsiveBehavior();
    }
    
    setupResponsiveBehavior() {
        const utils = new ResponsiveUtils();
        
        const updateChartSize = () => {
            let height;
            
            if (utils.isMobile()) {
                height = this.options.mobileHeight;
                this.chart.options.plugins.legend.position = 'bottom';
            } else if (utils.isTablet()) {
                height = this.options.tabletHeight;
                this.chart.options.plugins.legend.position = 'top';
            } else {
                height = this.options.desktopHeight;
                this.chart.options.plugins.legend.position = 'top';
            }
            
            this.element.style.height = height + 'px';
            this.chart.resize();
        };
        
        updateChartSize();
        window.addEventListener('breakpointChange', updateChartSize);
    }
}

/**
 * Initialize responsive utilities
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize global responsive utils
    window.responsiveUtils = new ResponsiveUtils();
    
    // Set initial breakpoint class
    document.body.classList.add(`breakpoint-${window.responsiveUtils.currentBreakpoint}`);
    
    // Initialize responsive tables
    document.querySelectorAll('.table-responsive table').forEach(table => {
        new ResponsiveTable(table);
    });
    
    // Initialize responsive forms
    document.querySelectorAll('.responsive-form').forEach(form => {
        new ResponsiveForm(form);
    });
    
    // Initialize responsive modals
    document.querySelectorAll('.modal').forEach(modal => {
        new ResponsiveModal(modal);
    });
    
    // Add CSS for responsive table indicators
    const style = document.createElement('style');
    style.textContent = `
        .table-responsive {
            position: relative;
        }
        
        .table-responsive::before,
        .table-responsive::after {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 20px;
            pointer-events: none;
            z-index: 1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .table-responsive::before {
            left: 0;
            background: linear-gradient(to right, rgba(0,0,0,0.1), transparent);
        }
        
        .table-responsive::after {
            right: 0;
            background: linear-gradient(to left, rgba(0,0,0,0.1), transparent);
        }
        
        .table-responsive.can-scroll-left::before,
        .table-responsive.can-scroll-right::after {
            opacity: 1;
        }
        
        @media (max-width: 767px) {
            .table-stacked {
                border: 0;
            }
            
            .table-stacked thead {
                display: none;
            }
            
            .table-stacked tbody tr {
                display: block;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                margin-bottom: 1rem;
                padding: 1rem;
                background: white;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            
            .table-stacked tbody td {
                display: block;
                text-align: left;
                border: none;
                padding: 0.5rem 0;
                position: relative;
                padding-left: 40%;
            }
            
            .table-stacked tbody td::before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 35%;
                font-weight: 600;
                color: #495057;
            }
        }
        
        .mobile-validation-error {
            font-size: 0.875rem;
            padding: 0.5rem;
            border-radius: 6px;
        }
    `;
    document.head.appendChild(style);
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        ResponsiveUtils,
        ResponsiveTable,
        ResponsiveForm,
        ResponsiveModal,
        ResponsiveChart
    };
}