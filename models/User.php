<?php
/**
 * Modelo de Usuário
 */

class User {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Lista usuários com filtros
     */
    public function getUsers($filters = [], $page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $where = [];
        $params = [];
        
        // Filtros
        if (!empty($filters['search'])) {
            $where[] = "(name LIKE ? OR email LIKE ? OR cpf LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "u.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "u.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Query principal
        $sql = "SELECT u.*, 
                       c.saldo as saldo_carteira,
                       (SELECT COUNT(*) FROM usuario_produtos up WHERE up.user_id = u.id AND up.status = 'ativo') as investimentos_ativos,
                       (SELECT SUM(valor_usd) FROM depositos d WHERE d.user_id = u.id AND d.status = 'confirmado') as total_depositado,
                       (SELECT SUM(valor_usd) FROM saques s WHERE s.user_id = u.id AND s.status = 'aprovado') as total_sacado
                FROM users u
                LEFT JOIN carteiras c ON c.user_id = u.id
                {$whereClause}
                ORDER BY u.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        
        $users = $this->db->fetchAll($sql, $params);
        
        // Count total
        $countSql = "SELECT COUNT(*) as total FROM users u {$whereClause}";
        $total = $this->db->fetch($countSql, $params)['total'];
        
        return [
            'data' => $users,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Busca usuário por ID
     */
    public function getUserById($id) {
        $sql = "SELECT u.*, 
                       c.saldo as saldo_carteira,
                       cc.saldo as saldo_comissao
                FROM users u
                LEFT JOIN carteiras c ON c.user_id = u.id
                LEFT JOIN carteiras_comissao cc ON cc.user_id = u.id
                WHERE u.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Atualiza usuário
     */
    public function updateUser($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['name', 'email', 'telefone', 'cpf', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Busca histórico de transações do usuário
     */
    public function getUserTransactions($userId, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM transacoes 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT {$limit} OFFSET {$offset}";
        
        $transactions = $this->db->fetchAll($sql, [$userId]);
        
        $countSql = "SELECT COUNT(*) as total FROM transacoes WHERE user_id = ?";
        $total = $this->db->fetch($countSql, [$userId])['total'];
        
        return [
            'data' => $transactions,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Busca investimentos do usuário
     */
    public function getUserInvestments($userId) {
        $sql = "SELECT up.*, p.nome as produto_nome
                FROM usuario_produtos up
                JOIN produtos p ON p.id = up.produto_id
                WHERE up.user_id = ?
                ORDER BY up.created_at DESC";
        
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    /**
     * Busca rede de afiliados
     */
    public function getAffiliateNetwork($userId, $level = 1, $maxLevel = 10) {
        if ($level > $maxLevel) {
            return [];
        }
        
        // Primeiro, buscar o código de indicação do usuário
        $userCode = $this->db->fetch(
            "SELECT codigo_indicacao FROM users WHERE id = ?",
            [$userId]
        );
        
        if (!$userCode) {
            return [];
        }
        
        $sql = "SELECT id, name, email, codigo_indicacao, codigo_indicador, created_at,
                       (SELECT saldo FROM carteiras WHERE user_id = users.id) as saldo,
                       (SELECT COUNT(*) FROM users WHERE codigo_indicador = users.codigo_indicacao) as direct_referrals
                FROM users 
                WHERE codigo_indicador = ?";
        
        $directReferrals = $this->db->fetchAll($sql, [$userCode['codigo_indicacao']]);
        
        $network = [];
        foreach ($directReferrals as $referral) {
            $referral['level'] = $level;
            $referral['children'] = $this->getAffiliateNetwork($referral['id'], $level + 1, $maxLevel);
            $network[] = $referral;
        }
        
        return $network;
    }
    
    /**
     * Estatísticas da rede de afiliados
     */
    public function getAffiliateStats($userId) {
        // Primeiro, buscar o código de indicação do usuário
        $userCode = $this->db->fetch(
            "SELECT codigo_indicacao FROM users WHERE id = ?",
            [$userId]
        );
        
        if (!$userCode) {
            return [];
        }
        
        $stats = [];
        
        for ($level = 1; $level <= 10; $level++) {
            // Usar uma abordagem mais simples para cada nível
            $result = $this->getStatsForLevel($userCode['codigo_indicacao'], $level, $userId);
            $stats[$level] = $result;
        }
        
        return $stats;
    }
    
    /**
     * Atualiza saldo da carteira
     */
    public function updateWalletBalance($userId, $amount, $operation = 'add') {
        $this->db->getConnection()->beginTransaction();
        
        try {
            // Verifica se carteira existe
            $wallet = $this->db->fetch("SELECT * FROM carteiras WHERE user_id = ?", [$userId]);
            
            if (!$wallet) {
                // Cria carteira se não existir
                $this->db->query("INSERT INTO carteiras (user_id, saldo, created_at) VALUES (?, 0, NOW())", [$userId]);
                $wallet = ['saldo' => 0];
            }
            
            // Verifica saldo suficiente para subtração
            if ($operation === 'subtract' && $wallet['saldo'] < $amount) {
                throw new Exception('Saldo insuficiente');
            }
            
            // Atualiza saldo
            if ($operation === 'add') {
                $sql = "UPDATE carteiras SET saldo = saldo + ?, updated_at = NOW() WHERE user_id = ?";
            } else {
                $sql = "UPDATE carteiras SET saldo = saldo - ?, updated_at = NOW() WHERE user_id = ?";
            }
            
            $this->db->query($sql, [$amount, $userId]);
            
            // Registra transação
            $tipo = ($operation === 'add') ? 'credito' : 'debito';
            $descricao = ($operation === 'add') ? 'Crédito administrativo' : 'Débito administrativo';
            
            $this->db->query(
                "INSERT INTO transacoes (user_id, tipo, valor, descricao, created_at) VALUES (?, ?, ?, ?, NOW())",
                [$userId, $tipo, $amount, $descricao]
            );
            
            $this->db->getConnection()->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollback();
            error_log("updateWalletBalance error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Busca estatísticas para um nível específico
     */
    private function getStatsForLevel($rootCode, $targetLevel, $rootUserId) {
        if ($targetLevel == 1) {
            // Nível 1: usuários diretamente indicados pelo usuário raiz
            $sql = "SELECT 
                        COUNT(DISTINCT u.id) as total_users,
                        COALESCE(SUM(d.valor_usd), 0) as total_deposits,
                        COALESCE(SUM(s.valor_usd), 0) as total_withdrawals,
                        COALESCE(SUM(up.valor_investido), 0) as total_investments,
                        COALESCE(SUM(c.valor), 0) as total_commissions
                    FROM users u
                    LEFT JOIN depositos d ON d.user_id = u.id AND d.status = 'confirmado'
                    LEFT JOIN saques s ON s.user_id = u.id AND s.status = 'aprovado'
                    LEFT JOIN usuario_produtos up ON up.user_id = u.id
                    LEFT JOIN comissoes c ON c.origem_user_id = u.id AND c.user_id = ?
                    WHERE u.codigo_indicador = ?";
            
            return $this->db->fetch($sql, [$rootUserId, $rootCode]);
        } else {
            // Para níveis superiores, usar recursão
            $prevLevelUsers = $this->getUsersAtLevel($rootCode, $targetLevel - 1);
            
            if (empty($prevLevelUsers)) {
                return [
                    'total_users' => 0,
                    'total_deposits' => 0,
                    'total_withdrawals' => 0,
                    'total_investments' => 0,
                    'total_commissions' => 0
                ];
            }
            
            $codes = array_column($prevLevelUsers, 'codigo_indicacao');
            $placeholders = str_repeat('?,', count($codes) - 1) . '?';
            
            $sql = "SELECT 
                        COUNT(DISTINCT u.id) as total_users,
                        COALESCE(SUM(d.valor_usd), 0) as total_deposits,
                        COALESCE(SUM(s.valor_usd), 0) as total_withdrawals,
                        COALESCE(SUM(up.valor_investido), 0) as total_investments,
                        COALESCE(SUM(c.valor), 0) as total_commissions
                    FROM users u
                    LEFT JOIN depositos d ON d.user_id = u.id AND d.status = 'confirmado'
                    LEFT JOIN saques s ON s.user_id = u.id AND s.status = 'aprovado'
                    LEFT JOIN usuario_produtos up ON up.user_id = u.id
                    LEFT JOIN comissoes c ON c.origem_user_id = u.id AND c.user_id = ?
                    WHERE u.codigo_indicador IN ($placeholders)";
            
            $params = array_merge([$rootUserId], $codes);
            return $this->db->fetch($sql, $params);
        }
    }
    
    /**
     * Busca usuários em um nível específico
     */
    private function getUsersAtLevel($rootCode, $level) {
        if ($level == 1) {
            return $this->db->fetchAll(
                "SELECT id, codigo_indicacao FROM users WHERE codigo_indicador = ?",
                [$rootCode]
            );
        } else {
            $prevLevelUsers = $this->getUsersAtLevel($rootCode, $level - 1);
            
            if (empty($prevLevelUsers)) {
                return [];
            }
            
            $codes = array_column($prevLevelUsers, 'codigo_indicacao');
            $placeholders = str_repeat('?,', count($codes) - 1) . '?';
            
            return $this->db->fetchAll(
                "SELECT id, codigo_indicacao FROM users WHERE codigo_indicador IN ($placeholders)",
                $codes
            );
        }
    }
}