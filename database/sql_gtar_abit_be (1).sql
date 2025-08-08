-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 08-Ago-2025 às 12:49
-- Versão do servidor: 10.11.6-MariaDB-log
-- versão do PHP: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de dados: `sql_gtar_abit_be`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `admin_users`
--

CREATE TABLE `admin_users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `ativos`
--

CREATE TABLE `ativos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(255) NOT NULL,
  `par` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `carteiras`
--

CREATE TABLE `carteiras` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `saldo` decimal(18,8) NOT NULL DEFAULT 0.00000000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `carteiras_comissao`
--

CREATE TABLE `carteiras_comissao` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `saldo` decimal(16,8) DEFAULT 0.00000000,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `comissoes`
--

CREATE TABLE `comissoes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `origem_user_id` bigint(20) UNSIGNED NOT NULL,
  `nivel` tinyint(4) NOT NULL,
  `valor` decimal(18,8) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `conta_saque_pix`
--

CREATE TABLE `conta_saque_pix` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(191) NOT NULL,
  `cpf_titular` varchar(14) NOT NULL,
  `tipo_chave` varchar(20) DEFAULT NULL,
  `chave_pix` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `conta_saque_usdt`
--

CREATE TABLE `conta_saque_usdt` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(191) NOT NULL,
  `endereco_carteira` varchar(191) NOT NULL,
  `rede` varchar(20) NOT NULL DEFAULT 'BEP20',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cupons_bonus`
--

CREATE TABLE `cupons_bonus` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(255) NOT NULL,
  `validade` date NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `valor_minimo` decimal(10,2) NOT NULL DEFAULT 0.10,
  `valor_maximo` decimal(10,2) NOT NULL DEFAULT 2.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cupons_usuarios`
--

CREATE TABLE `cupons_usuarios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `cupom_bonus_id` bigint(20) UNSIGNED NOT NULL,
  `valor_usd` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `depositos`
--

CREATE TABLE `depositos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `valor_usd` decimal(10,2) NOT NULL,
  `valor_brl` decimal(10,2) DEFAULT NULL,
  `txid` varchar(255) NOT NULL,
  `status` enum('pendente','confirmado','falhou') NOT NULL DEFAULT 'pendente',
  `cpf_informado` varchar(14) DEFAULT NULL,
  `tipo` enum('pix','usdt','usdc') NOT NULL,
  `destino` enum('investimento','negociacao') NOT NULL DEFAULT 'investimento',
  `valor_utilizado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `ordens`
--

CREATE TABLE `ordens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `ativo_id` bigint(20) UNSIGNED NOT NULL,
  `tipo` enum('compra','venda') NOT NULL,
  `quantidade` decimal(18,8) NOT NULL,
  `valor_unitario` decimal(18,8) NOT NULL,
  `status` enum('aberta','parcial','executada','cancelada') NOT NULL DEFAULT 'aberta',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `ordens_binarias`
--

CREATE TABLE `ordens_binarias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `par` varchar(20) NOT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `direcao` enum('cima','baixo') NOT NULL,
  `preco_entrada` decimal(15,8) NOT NULL,
  `stop_loss_price` decimal(15,8) DEFAULT NULL,
  `take_profit_price` decimal(15,8) DEFAULT NULL,
  `preco_saida` decimal(15,8) DEFAULT NULL,
  `resultado` enum('ganhou','perdeu','empate','em_andamento','stop_loss','take_profit') NOT NULL DEFAULT 'em_andamento',
  `aberta_em` timestamp NULL DEFAULT NULL,
  `expira_em` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `lucro_real` decimal(16,8) DEFAULT NULL COMMENT 'Lucro/prejuízo final calculado ao fechar a ordem',
  `lot_size` float NOT NULL DEFAULT 1,
  `risk_amount` decimal(16,8) NOT NULL DEFAULT 0.00000000,
  `profit_amount` decimal(16,8) NOT NULL DEFAULT 0.00000000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `ordens_simuladas`
--

CREATE TABLE `ordens_simuladas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `agente_id` bigint(20) UNSIGNED NOT NULL,
  `par_moeda` varchar(20) NOT NULL,
  `tipo_ordem` enum('COMPRA','VENDA') NOT NULL,
  `preco_entrada` decimal(18,6) NOT NULL,
  `preco_saida` decimal(18,6) NOT NULL,
  `horario_entrada` datetime NOT NULL,
  `horario_saida` datetime NOT NULL,
  `resultado` enum('profit','loss') NOT NULL,
  `percentual_variacao` decimal(6,2) NOT NULL,
  `lucro` decimal(10,4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `precos_reais`
--

CREATE TABLE `precos_reais` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `par_moeda` varchar(20) NOT NULL,
  `preco` decimal(18,6) NOT NULL,
  `timestamp` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(255) NOT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `comportamento` enum('conservador','moderado','agressivo') DEFAULT 'moderado',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `produto_ciclos`
--

CREATE TABLE `produto_ciclos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `agente_id` bigint(20) UNSIGNED NOT NULL,
  `dias` int(11) NOT NULL,
  `nivel` tinyint(4) NOT NULL,
  `tipo_contrato` enum('simulado','real') DEFAULT 'simulado',
  `rendimento` decimal(6,2) NOT NULL,
  `valor_minimo` decimal(18,2) NOT NULL,
  `valor_maximo` decimal(18,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rendimentos_diarios`
--

CREATE TABLE `rendimentos_diarios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `agente_id` bigint(20) UNSIGNED NOT NULL,
  `data` date NOT NULL,
  `rendimento_percentual` decimal(10,4) DEFAULT NULL,
  `acumulado_percentual` decimal(10,4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rendimentos_estatisticas`
--

CREATE TABLE `rendimentos_estatisticas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `agente_id` bigint(20) UNSIGNED NOT NULL,
  `data` date NOT NULL,
  `taxa_lucro_percentual` decimal(10,4) DEFAULT NULL,
  `lucro_bruto_total` decimal(12,4) DEFAULT NULL,
  `retorno_anual_percentual` decimal(10,4) DEFAULT NULL,
  `drawdown_percentual` decimal(10,4) DEFAULT NULL,
  `taxa_vitorias_percentual` decimal(10,4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `saques`
--

CREATE TABLE `saques` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `valor_usd` decimal(18,8) NOT NULL,
  `valor_brl` decimal(18,8) DEFAULT NULL,
  `tipo` enum('pix','usdt') NOT NULL DEFAULT 'pix',
  `status` enum('pendente','aprovado','rejeitado') NOT NULL DEFAULT 'pendente',
  `receiver_document` varchar(255) DEFAULT NULL,
  `receiver_name` varchar(255) DEFAULT NULL,
  `tipo_chave` enum('cpf','email','telefone','aleatoria') DEFAULT NULL,
  `chave_pix` varchar(255) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `rede` varchar(50) DEFAULT 'BEP20',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `solicitacoes_saque`
--

CREATE TABLE `solicitacoes_saque` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `valor` decimal(18,8) NOT NULL,
  `taxa` decimal(18,8) NOT NULL DEFAULT 0.00000000,
  `tipo` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pendente',
  `endereco_carteira` varchar(255) DEFAULT NULL,
  `rede` varchar(50) DEFAULT NULL,
  `external_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `trades`
--

CREATE TABLE `trades` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ordem_id` bigint(20) UNSIGNED NOT NULL,
  `quantidade_executada` decimal(18,8) NOT NULL,
  `valor_medio` decimal(18,8) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `transacoes`
--

CREATE TABLE `transacoes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `tipo` enum('credito','debito') NOT NULL,
  `valor` decimal(18,8) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `status` enum('ativo','bloqueado') NOT NULL DEFAULT 'ativo',
  `telefone` varchar(20) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `nivel_usuario` tinyint(4) NOT NULL DEFAULT 1,
  `codigo_indicacao` varchar(255) NOT NULL,
  `codigo_indicador` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `senha_saque` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuario_produtos`
--

CREATE TABLE `usuario_produtos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `produto_id` bigint(20) UNSIGNED NOT NULL,
  `agente_id` bigint(20) UNSIGNED DEFAULT NULL,
  `valor_investido` decimal(18,8) NOT NULL,
  `valor_bruto` decimal(18,8) DEFAULT NULL,
  `rendimento_liquido` decimal(18,6) DEFAULT 0.000000,
  `percentual` decimal(5,2) DEFAULT NULL,
  `rendimento_diario` decimal(6,4) DEFAULT NULL,
  `dias` int(11) DEFAULT NULL,
  `dias_concluidos` int(11) DEFAULT 0,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `encerrado_em` datetime DEFAULT NULL,
  `status` enum('ativo','finalizado') NOT NULL DEFAULT 'ativo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuario_rendimentos`
--

CREATE TABLE `usuario_rendimentos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_produto_id` bigint(20) UNSIGNED NOT NULL,
  `produto_id` bigint(20) UNSIGNED NOT NULL,
  `data` date NOT NULL,
  `percentual_dia` decimal(8,4) NOT NULL,
  `valor_dia` decimal(16,8) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_users_email_unique` (`email`);

--
-- Índices para tabela `ativos`
--
ALTER TABLE `ativos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Índices para tabela `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Índices para tabela `carteiras`
--
ALTER TABLE `carteiras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `carteiras_user_id_foreign` (`user_id`);

--
-- Índices para tabela `carteiras_comissao`
--
ALTER TABLE `carteiras_comissao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_carteiras_comissao_usuario` (`user_id`);

--
-- Índices para tabela `comissoes`
--
ALTER TABLE `comissoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comissoes_origem_user_id_foreign` (`origem_user_id`),
  ADD KEY `idx_foda_se` (`user_id`,`origem_user_id`,`tipo`);

--
-- Índices para tabela `conta_saque_pix`
--
ALTER TABLE `conta_saque_pix`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices para tabela `conta_saque_usdt`
--
ALTER TABLE `conta_saque_usdt`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices para tabela `cupons_bonus`
--
ALTER TABLE `cupons_bonus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Índices para tabela `cupons_usuarios`
--
ALTER TABLE `cupons_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cupons_usuarios_user_id_foreign` (`user_id`);

--
-- Índices para tabela `depositos`
--
ALTER TABLE `depositos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `depositos_txid_unique` (`txid`),
  ADD KEY `depositos_user_id_foreign` (`user_id`);

--
-- Índices para tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Índices para tabela `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Índices para tabela `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `ordens`
--
ALTER TABLE `ordens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ordens_user_id_foreign` (`user_id`),
  ADD KEY `ordens_ativo_id_foreign` (`ativo_id`);

--
-- Índices para tabela `ordens_binarias`
--
ALTER TABLE `ordens_binarias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices para tabela `ordens_simuladas`
--
ALTER TABLE `ordens_simuladas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Índices para tabela `precos_reais`
--
ALTER TABLE `precos_reais`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `produto_ciclos`
--
ALTER TABLE `produto_ciclos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `rendimentos_diarios`
--
ALTER TABLE `rendimentos_diarios`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `rendimentos_estatisticas`
--
ALTER TABLE `rendimentos_estatisticas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_estatisticas_agente` (`agente_id`);

--
-- Índices para tabela `saques`
--
ALTER TABLE `saques`
  ADD PRIMARY KEY (`id`),
  ADD KEY `saques_user_id_foreign` (`user_id`);

--
-- Índices para tabela `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Índices para tabela `solicitacoes_saque`
--
ALTER TABLE `solicitacoes_saque`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `trades`
--
ALTER TABLE `trades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trades_ordem_id_foreign` (`ordem_id`);

--
-- Índices para tabela `transacoes`
--
ALTER TABLE `transacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transacoes_user_id_foreign` (`user_id`);

--
-- Índices para tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_codigo_indicacao_unique` (`codigo_indicacao`);

--
-- Índices para tabela `usuario_produtos`
--
ALTER TABLE `usuario_produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_produtos_user_id_foreign` (`user_id`),
  ADD KEY `usuario_produtos_produto_id_foreign` (`produto_id`);

--
-- Índices para tabela `usuario_rendimentos`
--
ALTER TABLE `usuario_rendimentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_produto_id` (`usuario_produto_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ativos`
--
ALTER TABLE `ativos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `carteiras`
--
ALTER TABLE `carteiras`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `carteiras_comissao`
--
ALTER TABLE `carteiras_comissao`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `comissoes`
--
ALTER TABLE `comissoes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `conta_saque_pix`
--
ALTER TABLE `conta_saque_pix`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `conta_saque_usdt`
--
ALTER TABLE `conta_saque_usdt`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cupons_bonus`
--
ALTER TABLE `cupons_bonus`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cupons_usuarios`
--
ALTER TABLE `cupons_usuarios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `depositos`
--
ALTER TABLE `depositos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ordens`
--
ALTER TABLE `ordens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ordens_binarias`
--
ALTER TABLE `ordens_binarias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ordens_simuladas`
--
ALTER TABLE `ordens_simuladas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `precos_reais`
--
ALTER TABLE `precos_reais`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `produto_ciclos`
--
ALTER TABLE `produto_ciclos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `rendimentos_diarios`
--
ALTER TABLE `rendimentos_diarios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `rendimentos_estatisticas`
--
ALTER TABLE `rendimentos_estatisticas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `saques`
--
ALTER TABLE `saques`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `solicitacoes_saque`
--
ALTER TABLE `solicitacoes_saque`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `trades`
--
ALTER TABLE `trades`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `transacoes`
--
ALTER TABLE `transacoes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuario_produtos`
--
ALTER TABLE `usuario_produtos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuario_rendimentos`
--
ALTER TABLE `usuario_rendimentos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `carteiras`
--
ALTER TABLE `carteiras`
  ADD CONSTRAINT `carteiras_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `carteiras_comissao`
--
ALTER TABLE `carteiras_comissao`
  ADD CONSTRAINT `fk_carteiras_comissao_usuario` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `comissoes`
--
ALTER TABLE `comissoes`
  ADD CONSTRAINT `comissoes_origem_user_id_foreign` FOREIGN KEY (`origem_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comissoes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `conta_saque_pix`
--
ALTER TABLE `conta_saque_pix`
  ADD CONSTRAINT `conta_saque_pix_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `conta_saque_usdt`
--
ALTER TABLE `conta_saque_usdt`
  ADD CONSTRAINT `conta_saque_usdt_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `cupons_usuarios`
--
ALTER TABLE `cupons_usuarios`
  ADD CONSTRAINT `cupons_usuarios_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `depositos`
--
ALTER TABLE `depositos`
  ADD CONSTRAINT `depositos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Limitadores para a tabela `ordens`
--
ALTER TABLE `ordens`
  ADD CONSTRAINT `ordens_ativo_id_foreign` FOREIGN KEY (`ativo_id`) REFERENCES `ativos` (`id`),
  ADD CONSTRAINT `ordens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Limitadores para a tabela `ordens_binarias`
--
ALTER TABLE `ordens_binarias`
  ADD CONSTRAINT `ordens_binarias_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `rendimentos_estatisticas`
--
ALTER TABLE `rendimentos_estatisticas`
  ADD CONSTRAINT `fk_estatisticas_agente` FOREIGN KEY (`agente_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `saques`
--
ALTER TABLE `saques`
  ADD CONSTRAINT `saques_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Limitadores para a tabela `trades`
--
ALTER TABLE `trades`
  ADD CONSTRAINT `trades_ordem_id_foreign` FOREIGN KEY (`ordem_id`) REFERENCES `ordens` (`id`);

--
-- Limitadores para a tabela `transacoes`
--
ALTER TABLE `transacoes`
  ADD CONSTRAINT `transacoes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `usuario_produtos`
--
ALTER TABLE `usuario_produtos`
  ADD CONSTRAINT `usuario_produtos_produto_id_foreign` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`),
  ADD CONSTRAINT `usuario_produtos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Limitadores para a tabela `usuario_rendimentos`
--
ALTER TABLE `usuario_rendimentos`
  ADD CONSTRAINT `usuario_rendimentos_ibfk_1` FOREIGN KEY (`usuario_produto_id`) REFERENCES `usuario_produtos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
