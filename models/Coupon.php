<?php
/**
 * Modelo de Cupons
 */

class Coupon {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Lista cupons com filtros
     */
    public function getCoupons($filters = [], $page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $where = [];
        $params = [];
        
        // Filtros
        if (!empty($filters['search'])) {
            $where[] = "codigo LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }
        
        if (isset($filters['ativo'])) {
            $where[] = "ativo = ?";
            $params[] = $filters['ativo'];
        }
        
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'valido') {
                $where[] = "validade >= NOW()";
            } elseif ($filters['status'] === 'expirado') {
                $where[] = "validade < NOW()";
            }
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT cb.*,
                       (SELECT COUNT(*) FROM cupons_usuarios cu WHERE cu.cupom_bonus_id = cb.id) as total_resgates,
                       (SELECT COALESCE(SUM(cu.valor_usd), 0) FROM cupons_usuarios cu WHERE cu.cupom_bonus_id = cb.id) as valor_total_resgatado,
                       CASE 
                           WHEN cb.validade >= CURDATE() THEN 'valido'
                           ELSE 'expirado'
                       END as status_validade
                FROM cupons_bonus cb
                {$whereClause}
                ORDER BY cb.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        
        $coupons = $this->db->fetchAll($sql, $params);
        
        // Count total
        $countSql = "SELECT COUNT(*) as total FROM cupons_bonus cb {$whereClause}";
        $total = $this->db->fetch($countSql, $params)['total'];
        
        return [
            'data' => $coupons,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Busca cupom por ID
     */
    public function getCouponById($id) {
        $sql = "SELECT cb.*,
                       (SELECT COUNT(*) FROM cupons_usuarios cu WHERE cu.cupom_bonus_id = cb.id) as total_resgates,
                       (SELECT COALESCE(SUM(cu.valor_usd), 0) FROM cupons_usuarios cu WHERE cu.cupom_bonus_id = cb.id) as valor_total_resgatado,
                       CASE 
                           WHEN cb.validade >= CURDATE() THEN 'valido'
                           ELSE 'expirado'
                       END as status_validade
                FROM cupons_bonus cb
                WHERE cb.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Cria cupom
     */
    public function createCoupon($data) {
        // Verifica se código já existe
        $existing = $this->db->fetch(
            "SELECT id FROM cupons_bonus WHERE codigo = ?",
            [$data['codigo']]
        );
        
        if ($existing) {
            throw new Exception("Código de cupom já existe");
        }
        
        $sql = "INSERT INTO cupons_bonus (codigo, validade, ativo, valor_minimo, valor_maximo, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        
        return $this->db->query($sql, [
            $data['codigo'],
            $data['validade'],
            $data['ativo'] ?? 1,
            $data['valor_minimo'],
            $data['valor_maximo']
        ]);
    }
    
    /**
     * Atualiza cupom
     */
    public function updateCoupon($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['codigo', 'validade', 'ativo', 'valor_minimo', 'valor_maximo'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                // Verifica se código já existe (exceto para o próprio cupom)
                if ($field === 'codigo') {
                    $existing = $this->db->fetch(
                        "SELECT id FROM cupons_bonus WHERE codigo = ? AND id != ?",
                        [$data[$field], $id]
                    );
                    
                    if ($existing) {
                        throw new Exception("Código de cupom já existe");
                    }
                }
                
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        
        $sql = "UPDATE cupons_bonus SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Exclui cupom
     */
    public function deleteCoupon($id) {
        // Verifica se há resgates
        $redemptions = $this->db->fetch(
            "SELECT COUNT(*) as count FROM cupons_usuarios WHERE cupom_bonus_id = ?",
            [$id]
        );
        
        if ($redemptions['count'] > 0) {
            throw new Exception("Não é possível excluir cupom que já foi resgatado");
        }
        
        return $this->db->query("DELETE FROM cupons_bonus WHERE id = ?", [$id]);
    }
    
    /**
     * Busca resgates do cupom
     */
    public function getCouponRedemptions($couponId, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT cu.*, u.name as user_name, u.email as user_email, u.codigo_indicacao
                FROM cupons_usuarios cu
                JOIN users u ON u.id = cu.user_id
                WHERE cu.cupom_bonus_id = ?
                ORDER BY cu.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        
        $redemptions = $this->db->fetchAll($sql, [$couponId]);
        
        // Count total
        $countSql = "SELECT COUNT(*) as total FROM cupons_usuarios WHERE cupom_bonus_id = ?";
        $total = $this->db->fetch($countSql, [$couponId])['total'];
        
        return [
            'data' => $redemptions,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Gera código único para cupom
     */
    public function generateUniqueCode($prefix = 'CUPOM') {
        do {
            $code = $prefix . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
            $existing = $this->db->fetch("SELECT id FROM cupons_bonus WHERE codigo = ?", [$code]);
        } while ($existing);
        
        return $code;
    }
    
    /**
     * Estatísticas dos cupons
     */
    public function getCouponStats() {
        $stats = $this->db->fetch("
            SELECT 
                COUNT(*) as total_cupons,
                COUNT(CASE WHEN ativo = 1 THEN 1 END) as cupons_ativos,
                COUNT(CASE WHEN validade >= NOW() THEN 1 END) as cupons_validos,
                COUNT(CASE WHEN validade < NOW() THEN 1 END) as cupons_expirados,
                (SELECT COUNT(*) FROM cupons_usuarios) as total_resgates,
                (SELECT COALESCE(SUM(valor_usd), 0) FROM cupons_usuarios) as valor_total_resgatado
            FROM cupons_bonus
        ");
        
        return $stats;
    }
}