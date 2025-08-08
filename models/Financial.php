<?php
/**
 * Modelo Financeiro
 */

class Financial {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Lista depósitos com filtros
     */
    public function getDeposits($filters = [], $page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $where = [];
        $params = [];
        
        // Filtros
        if (!empty($filters['status'])) {
            $where[] = "d.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['tipo'])) {
            $where[] = "d.tipo = ?";
            $params[] = $filters['tipo'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "d.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "d.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(u.name LIKE ? OR u.email LIKE ? OR d.txid LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT d.*, u.name as user_name, u.email as user_email
                FROM depositos d
                JOIN users u ON u.id = d.user_id
                {$whereClause}
                ORDER BY d.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        
        $deposits = $this->db->fetchAll($sql, $params);
        
        // Count total
        $countSql = "SELECT COUNT(*) as total FROM depositos d JOIN users u ON u.id = d.user_id {$whereClause}";
        $total = $this->db->fetch($countSql, $params)['total'];
        
        return [
            'data' => $deposits,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Lista saques com filtros
     */
    public function getWithdrawals($filters = [], $page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $where = [];
        $params = [];
        
        // Filtros
        if (!empty($filters['status'])) {
            $where[] = "s.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['tipo'])) {
            $where[] = "s.tipo = ?";
            $params[] = $filters['tipo'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "s.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "s.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(u.name LIKE ? OR u.email LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT s.*, u.name as user_name, u.email as user_email
                FROM saques s
                JOIN users u ON u.id = s.user_id
                {$whereClause}
                ORDER BY s.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        
        $withdrawals = $this->db->fetchAll($sql, $params);
        
        // Count total
        $countSql = "SELECT COUNT(*) as total FROM saques s JOIN users u ON u.id = s.user_id {$whereClause}";
        $total = $this->db->fetch($countSql, $params)['total'];
        
        return [
            'data' => $withdrawals,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Atualiza status do depósito
     */
    public function updateDepositStatus($id, $status, $adminId) {
        $this->db->getConnection()->beginTransaction();
        
        try {
            // Busca depósito
            $deposit = $this->db->fetch("SELECT * FROM depositos WHERE id = ?", [$id]);
            if (!$deposit) {
                throw new Exception("Depósito não encontrado");
            }
            
            $oldStatus = $deposit['status'];
            
            // Atualiza status
            $this->db->query(
                "UPDATE depositos SET status = ?, updated_at = NOW() WHERE id = ?",
                [$status, $id]
            );
            
            // Se aprovado, credita na carteira
            if ($status === 'confirmado' && $oldStatus !== 'confirmado') {
                $userModel = new User();
                $userModel->updateWalletBalance($deposit['user_id'], $deposit['valor_usd'], 'add');
                
                // Registra log
                logAction(
                    'DEPOSIT_APPROVED',
                    "Depósito #{$id} aprovado - Valor: {$deposit['valor_usd']} USD - Usuário: {$deposit['user_id']}",
                    $adminId
                );
            }
            
            // Se rejeitado após ter sido confirmado, debita da carteira
            if ($status === 'falhou' && $oldStatus === 'confirmado') {
                $userModel = new User();
                $userModel->updateWalletBalance($deposit['user_id'], $deposit['valor_usd'], 'subtract');
                
                // Registra log
                logAction(
                    'DEPOSIT_REJECTED',
                    "Depósito #{$id} rejeitado - Valor: {$deposit['valor_usd']} USD - Usuário: {$deposit['user_id']}",
                    $adminId
                );
            }
            
            $this->db->getConnection()->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollback();
            throw $e;
        }
    }
    
    /**
     * Atualiza status do saque
     */
    public function updateWithdrawalStatus($id, $status, $adminId) {
        $this->db->getConnection()->beginTransaction();
        
        try {
            // Busca saque
            $withdrawal = $this->db->fetch("SELECT * FROM saques WHERE id = ?", [$id]);
            if (!$withdrawal) {
                throw new Exception("Saque não encontrado");
            }
            
            $oldStatus = $withdrawal['status'];
            
            // Atualiza status
            $this->db->query(
                "UPDATE saques SET status = ?, updated_at = NOW() WHERE id = ?",
                [$status, $id]
            );
            
            // Se rejeitado, reembolsa na carteira
            if ($status === 'rejeitado' && $oldStatus === 'pendente') {
                $userModel = new User();
                $userModel->updateWalletBalance($withdrawal['user_id'], $withdrawal['valor_usd'], 'add');
                
                // Registra log
                logAction(
                    'WITHDRAWAL_REJECTED',
                    "Saque #{$id} rejeitado - Valor: {$withdrawal['valor_usd']} USD - Usuário: {$withdrawal['user_id']}",
                    $adminId
                );
            }
            
            if ($status === 'aprovado') {
                logAction(
                    'WITHDRAWAL_APPROVED',
                    "Saque #{$id} aprovado - Valor: {$withdrawal['valor_usd']} USD - Usuário: {$withdrawal['user_id']}",
                    $adminId
                );
            }
            
            $this->db->getConnection()->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollback();
            throw $e;
        }
    }
    
    /**
     * Relatório financeiro
     */
    public function getFinancialReport($dateFrom, $dateTo) {
        $params = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];
        
        // Depósitos
        $depositsSql = "SELECT 
                           COUNT(*) as total_deposits,
                           COALESCE(SUM(valor_usd), 0) as total_deposit_amount,
                           COUNT(CASE WHEN status = 'confirmado' THEN 1 END) as confirmed_deposits,
                           COALESCE(SUM(CASE WHEN status = 'confirmado' THEN valor_usd END), 0) as confirmed_amount
                        FROM depositos 
                        WHERE created_at BETWEEN ? AND ?";
        
        $deposits = $this->db->fetch($depositsSql, $params);
        
        // Saques
        $withdrawalsSql = "SELECT 
                              COUNT(*) as total_withdrawals,
                              COALESCE(SUM(valor_usd), 0) as total_withdrawal_amount,
                              COUNT(CASE WHEN status = 'aprovado' THEN 1 END) as approved_withdrawals,
                              COALESCE(SUM(CASE WHEN status = 'aprovado' THEN valor_usd END), 0) as approved_amount
                           FROM saques 
                           WHERE created_at BETWEEN ? AND ?";
        
        $withdrawals = $this->db->fetch($withdrawalsSql, $params);
        
        // Investimentos
        $investmentsSql = "SELECT 
                              COUNT(*) as total_investments,
                              COALESCE(SUM(valor_investido), 0) as total_investment_amount,
                              COUNT(CASE WHEN status = 'ativo' THEN 1 END) as active_investments,
                              COALESCE(SUM(CASE WHEN status = 'ativo' THEN valor_investido END), 0) as active_amount
                           FROM usuario_produtos 
                           WHERE created_at BETWEEN ? AND ?";
        
        $investments = $this->db->fetch($investmentsSql, $params);
        
        // Comissões
        $commissionsSql = "SELECT 
                              COUNT(*) as total_commissions,
                              COALESCE(SUM(valor), 0) as total_commission_amount
                           FROM comissoes 
                           WHERE created_at BETWEEN ? AND ?";
        
        $commissions = $this->db->fetch($commissionsSql, $params);
        
        return [
            'deposits' => $deposits,
            'withdrawals' => $withdrawals,
            'investments' => $investments,
            'commissions' => $commissions,
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ];
    }
    
    /**
     * Dashboard stats
     */
    public function getDashboardStats() {
        // Usuários
        $userStats = $this->db->fetch("
            SELECT 
                COUNT(*) as total_users,
                COUNT(CASE WHEN status = 'ativo' THEN 1 END) as active_users,
                COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as new_today
            FROM users
        ");
        
        // Financeiro
        $financialStats = $this->db->fetch("
            SELECT 
                (SELECT COALESCE(SUM(valor_usd), 0) FROM depositos WHERE status = 'confirmado') as total_deposits,
                (SELECT COALESCE(SUM(valor_usd), 0) FROM saques WHERE status = 'aprovado') as total_withdrawals,
                (SELECT COALESCE(SUM(valor_investido), 0) FROM usuario_produtos WHERE status = 'ativo') as total_investments,
                (SELECT COALESCE(SUM(saldo), 0) FROM carteiras) as total_balance
        ");
        
        // Pendências
        $pendingStats = $this->db->fetch("
            SELECT 
                (SELECT COUNT(*) FROM depositos WHERE status = 'pendente') as pending_deposits,
                (SELECT COUNT(*) FROM saques WHERE status = 'pendente') as pending_withdrawals
        ");
        
        return [
            'users' => $userStats,
            'financial' => $financialStats,
            'pending' => $pendingStats
        ];
    }
}