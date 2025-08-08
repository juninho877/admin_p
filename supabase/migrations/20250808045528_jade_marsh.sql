-- Tabelas administrativas adicionais

-- Tabela de logs administrativos
CREATE TABLE IF NOT EXISTS `admin_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_admin_logs_admin_id` (`admin_id`),
  KEY `idx_admin_logs_action` (`action`),
  KEY `idx_admin_logs_created_at` (`created_at`),
  CONSTRAINT `fk_admin_logs_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de tentativas de login
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_login_attempts_email` (`email`),
  KEY `idx_login_attempts_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir admin padrão (senha: admin123)
INSERT IGNORE INTO `admin_users` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'Administrador', 'admin@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW());

-- Índices adicionais para otimização
ALTER TABLE `users` ADD INDEX `idx_users_status` (`status`);
ALTER TABLE `users` ADD INDEX `idx_users_created_at` (`created_at`);
ALTER TABLE `depositos` ADD INDEX `idx_depositos_status` (`status`);
ALTER TABLE `depositos` ADD INDEX `idx_depositos_created_at` (`created_at`);
ALTER TABLE `saques` ADD INDEX `idx_saques_status` (`status`);
ALTER TABLE `saques` ADD INDEX `idx_saques_created_at` (`created_at`);
ALTER TABLE `usuario_produtos` ADD INDEX `idx_usuario_produtos_status` (`status`);
ALTER TABLE `comissoes` ADD INDEX `idx_comissoes_created_at` (`created_at`);