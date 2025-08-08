<?php
/**
 * Modelo de Produtos/Planos
 */

class Product {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Lista produtos
     */
    public function getProducts($filters = [], $page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $where = [];
        $params = [];
        
        if (!empty($filters['search'])) {
            $where[] = "nome LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }
        
        if (isset($filters['ativo'])) {
            $where[] = "ativo = ?";
            $params[] = $filters['ativo'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT p.*,
                       (SELECT COUNT(*) FROM usuario_produtos up WHERE up.produto_id = p.id AND up.status = 'ativo') as active_investments,
                       (SELECT COALESCE(SUM(up.valor_investido), 0) FROM usuario_produtos up WHERE up.produto_id = p.id) as total_invested
                FROM produtos p
                {$whereClause}
                ORDER BY p.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        
        $products = $this->db->fetchAll($sql, $params);
        
        // Count total
        $countSql = "SELECT COUNT(*) as total FROM produtos p {$whereClause}";
        $total = $this->db->fetch($countSql, $params)['total'];
        
        return [
            'data' => $products,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Busca produto por ID
     */
    public function getProductById($id) {
        $sql = "SELECT p.*,
                       (SELECT COUNT(*) FROM usuario_produtos up WHERE up.produto_id = p.id AND up.status = 'ativo') as active_investments,
                       (SELECT COALESCE(SUM(up.valor_investido), 0) FROM usuario_produtos up WHERE up.produto_id = p.id) as total_invested
                FROM produtos p
                WHERE p.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Cria produto
     */
    public function createProduct($data) {
        $sql = "INSERT INTO produtos (nome, avatar_url, comportamento, ativo, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        return $this->db->query($sql, [
            $data['nome'],
            $data['avatar_url'] ?? null,
            $data['comportamento'] ?? 'moderado',
            $data['ativo'] ?? 1
        ]);
    }
    
    /**
     * Atualiza produto
     */
    public function updateProduct($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['nome', 'avatar_url', 'comportamento', 'ativo'];
        
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
        
        $sql = "UPDATE produtos SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Exclui produto
     */
    public function deleteProduct($id) {
        // Verifica se há investimentos ativos
        $activeInvestments = $this->db->fetch(
            "SELECT COUNT(*) as count FROM usuario_produtos WHERE produto_id = ? AND status = 'ativo'",
            [$id]
        );
        
        if ($activeInvestments['count'] > 0) {
            throw new Exception("Não é possível excluir produto com investimentos ativos");
        }
        
        return $this->db->query("DELETE FROM produtos WHERE id = ?", [$id]);
    }
    
    /**
     * Lista ciclos do produto
     */
    public function getProductCycles($productId) {
        $sql = "SELECT * FROM produto_ciclos WHERE agente_id = ? ORDER BY nivel ASC";
        return $this->db->fetchAll($sql, [$productId]);
    }
    
    /**
     * Cria ciclo do produto
     */
    public function createProductCycle($data) {
        $sql = "INSERT INTO produto_ciclos (agente_id, dias, nivel, tipo_contrato, rendimento, valor_minimo, valor_maximo, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        return $this->db->query($sql, [
            $data['agente_id'],
            $data['dias'],
            $data['nivel'],
            $data['tipo_contrato'] ?? 'simulado',
            $data['rendimento'],
            $data['valor_minimo'],
            $data['valor_maximo']
        ]);
    }
    
    /**
     * Atualiza ciclo do produto
     */
    public function updateProductCycle($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['dias', 'nivel', 'tipo_contrato', 'rendimento', 'valor_minimo', 'valor_maximo'];
        
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
        
        $sql = "UPDATE produto_ciclos SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Lista investimentos do produto
     */
    public function getProductInvestments($productId, $page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT up.*, u.name as user_name, u.email as user_email
                FROM usuario_produtos up
                JOIN users u ON u.id = up.user_id
                WHERE up.produto_id = ?
                ORDER BY up.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        
        $investments = $this->db->fetchAll($sql, [$productId]);
        
        // Count total
        $countSql = "SELECT COUNT(*) as total FROM usuario_produtos WHERE produto_id = ?";
        $total = $this->db->fetch($countSql, [$productId])['total'];
        
        return [
            'data' => $investments,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Finaliza investimento
     */
    public function finalizeInvestment($investmentId, $adminId) {
        $this->db->getConnection()->beginTransaction();
        
        try {
            // Busca investimento
            $investment = $this->db->fetch("SELECT * FROM usuario_produtos WHERE id = ?", [$investmentId]);
            if (!$investment) {
                throw new Exception("Investimento não encontrado");
            }
            
            if ($investment['status'] !== 'ativo') {
                throw new Exception("Investimento não está ativo");
            }
            
            // Calcula valor final
            $valorFinal = $investment['valor_investido'] + $investment['rendimento_liquido'];
            
            // Atualiza investimento
            $this->db->query(
                "UPDATE usuario_produtos SET status = 'finalizado', encerrado_em = NOW(), updated_at = NOW() WHERE id = ?",
                [$investmentId]
            );
            
            // Credita na carteira
            $userModel = new User();
            $userModel->updateWalletBalance($investment['user_id'], $valorFinal, 'add');
            
            // Registra log
            logAction(
                'INVESTMENT_FINALIZED',
                "Investimento #{$investmentId} finalizado - Valor: {$valorFinal} USD - Usuário: {$investment['user_id']}",
                $adminId
            );
            
            $this->db->getConnection()->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollback();
            throw $e;
        }
    }
}