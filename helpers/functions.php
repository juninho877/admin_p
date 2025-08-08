<?php
/**
 * Funções Auxiliares do Sistema
 */

/**
 * Sanitiza dados de entrada
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Formata valores monetários
 */
function formatMoney($value, $currency = 'BRL') {
    if ($currency === 'BRL') {
        return 'R$ ' . number_format($value, 2, ',', '.');
    } else {
        return '$ ' . number_format($value, 2, '.', ',');
    }
}

/**
 * Formata datas
 */
function formatDate($date, $format = 'd/m/Y H:i:s') {
    if (empty($date)) return '-';
    return date($format, strtotime($date));
}

/**
 * Gera código único
 */
function generateUniqueCode($length = 8) {
    return strtoupper(substr(md5(uniqid(rand(), true)), 0, $length));
}

/**
 * Valida CPF
 */
function validateCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    
    return true;
}

/**
 * Valida email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Gera paginação
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    $html = '<nav aria-label="Paginação">';
    $html .= '<ul class="pagination justify-content-center">';
    
    // Botão anterior
    if ($currentPage > 1) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $baseUrl . '&page=' . ($currentPage - 1) . '">Anterior</a>';
        $html .= '</li>';
    }
    
    // Números das páginas
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $active = ($i == $currentPage) ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '">';
        $html .= '<a class="page-link" href="' . $baseUrl . '&page=' . $i . '">' . $i . '</a>';
        $html .= '</li>';
    }
    
    // Botão próximo
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $baseUrl . '&page=' . ($currentPage + 1) . '">Próximo</a>';
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Registra log de ação
 */
function logAction($action, $details = '', $userId = null) {
    try {
        $db = new Database();
        
        if (!$userId && isset($_SESSION['admin_id'])) {
            $userId = $_SESSION['admin_id'];
        }
        
        $sql = "INSERT INTO admin_logs (admin_id, action, details, ip_address, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $db->query($sql, [$userId, $action, $details, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    } catch (Exception $e) {
        error_log("Error logging action: " . $e->getMessage());
        // Não propaga o erro para não quebrar a operação principal
    }
}

/**
 * Envia notificação
 */
function sendNotification($type, $message) {
    $_SESSION['notification'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Exibe notificação
 */
function showNotification() {
    if (isset($_SESSION['notification'])) {
        $notification = $_SESSION['notification'];
        $alertClass = '';
        
        switch ($notification['type']) {
            case 'success':
                $alertClass = 'alert-success';
                break;
            case 'error':
                $alertClass = 'alert-danger';
                break;
            case 'warning':
                $alertClass = 'alert-warning';
                break;
            case 'info':
                $alertClass = 'alert-info';
                break;
        }
        
        echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
        echo $notification['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        
        unset($_SESSION['notification']);
    }
}