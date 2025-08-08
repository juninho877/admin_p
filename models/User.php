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
            $where[] = "created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "created_at <= ?";
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
        
        $sql = "SELECT id, name, email, codigo_indicacao, created_at,
                       (SELECT saldo FROM carteiras WHERE user_id = users.id) as saldo,
                       (SELECT COUNT(*) FROM users WHERE codigo_indicador = users.codigo_indicacao) as direct_referrals
                FROM users 
                WHERE codigo_indicador = (SELECT codigo_indicacao FROM users WHERE id = ?)";
        
        $directReferrals = $this->db->fetchAll($sql, [$userId]);
        
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
        $stats = [];
        
        for ($level = 1; $level <= 10; $level++) {
            $sql = "WITH RECURSIVE affiliate_tree AS (
                        SELECT id, codigo_indicacao, 1 as level
                        FROM users 
                        WHERE codigo_indicador = (SELECT codigo_indicacao FROM users WHERE id = ?)
                        
                        UNION ALL
                        
                        SELECT u.id, u.codigo_indicacao, at.level + 1
                        FROM users u
                        INNER JOIN affiliate_tree at ON u.codigo_indicador = at.codigo_indicacao
                        WHERE at.level < ?
                    )
                    SELECT 
                        COUNT(*) as total_users,
                        COALESCE(SUM(d.valor_usd), 0) as total_deposits,
                        COALESCE(SUM(s.valor_usd), 0) as total_withdrawals,
                        COALESCE(SUM(up.valor_investido), 0) as total_investments,
                        COALESCE(SUM(c.valor), 0) as total_commissions
                    FROM affiliate_tree at
                    LEFT JOIN depositos d ON d.user_id = at.id AND d.status = 'confirmado'
                    LEFT JOIN saques s ON s.user_id = at.id AND s.status = 'aprovado'
                    LEFT JOIN usuario_produtos up ON up.user_id = at.id
                    LEFT JOIN comissoes c ON c.origem_user_id = at.id AND c.user_id = ?
                    WHERE at.level = ?";
            
            $result = $this->db->fetch($sql, [$userId, $level, $userId, $level]);
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
            throw $e;
        }
    }
}