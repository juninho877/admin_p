<?php
require_once '../config/config.php';
requireAdminLogin();

header('Content-Type: application/json');

try {
    $couponId = (int)($_GET['coupon_id'] ?? 0);
    
    if (!$couponId) {
        throw new Exception('ID do cupom não fornecido');
    }
    
    $couponModel = new Coupon();
    
    // Verifica se cupom existe
    $coupon = $couponModel->getCouponById($couponId);
    if (!$coupon) {
        throw new Exception('Cupom não encontrado');
    }
    
    // Busca resgates
    $result = $couponModel->getCouponRedemptions($couponId, 1, 100); // Buscar até 100 resgates
    
    echo json_encode([
        'success' => true,
        'coupon' => $coupon,
        'redemptions' => $result['data'],
        'total' => $result['total']
    ]);
    
} catch (Exception $e) {
    error_log("Get coupon redemptions error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}