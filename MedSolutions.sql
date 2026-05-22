-- ============================================================
-- MedSolutions Database Schema
-- Baseado na estrutura SIBDAS do isep-ginasio
-- ============================================================

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- ============================================================
-- Criar base de dados
-- ============================================================
DROP DATABASE IF EXISTS `MedSolutions`;
CREATE DATABASE `MedSolutions` 
USE `MedSolutions`;


DROP TABLE IF EXISTS `agents`;
CREATE TABLE IF NOT EXISTS `agents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARBINARY(200) DEFAULT NULL,
  `passwrd` VARCHAR(200) DEFAULT NULL,
  `profile` VARCHAR(20) DEFAULT NULL,
  `purl` VARCHAR(20) DEFAULT NULL,
  `code` VARCHAR(6) DEFAULT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `descricao` TEXT DEFAULT NULL,
  `ativo` TINYINT UNSIGNED DEFAULT 1,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `localizacoes`;
CREATE TABLE IF NOT EXISTS `localizacoes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `edificio` VARCHAR(100) DEFAULT NULL,
  `piso` VARCHAR(50) DEFAULT NULL,
  `servico` VARCHAR(100) NOT NULL,
  `sala` VARCHAR(100) DEFAULT NULL,
  `observacoes` TEXT DEFAULT NULL,
  `ativo` TINYINT UNSIGNED DEFAULT 1,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `fornecedores`;
CREATE TABLE IF NOT EXISTS `fornecedores` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `nif` VARCHAR(9) DEFAULT NULL,
  `telefone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `morada` VARCHAR(200) DEFAULT NULL,
  `website` VARCHAR(200) DEFAULT NULL,
  `pessoa_contacto` VARCHAR(100) DEFAULT NULL,
  `tel_contacto` VARCHAR(20) DEFAULT NULL,
  `tipo_fornecedor` VARCHAR(50) DEFAULT NULL,
  `observacoes` TEXT DEFAULT NULL,
  `ativo` TINYINT UNSIGNED DEFAULT 1,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `equipamentos`;
CREATE TABLE IF NOT EXISTS `equipamentos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo_inventario` VARCHAR(50) NOT NULL,
  `designacao` VARCHAR(200) NOT NULL,
  `id_categoria` INT UNSIGNED DEFAULT NULL,
  `marca` VARCHAR(100) DEFAULT NULL,
  `modelo` VARCHAR(100) DEFAULT NULL,
  `numero_serie` VARCHAR(100) DEFAULT NULL,
  `fabricante` VARCHAR(150) DEFAULT NULL,
  `data_aquisicao` DATE DEFAULT NULL,
  `ano_fabrico` YEAR DEFAULT NULL,
  `custo_aquisicao` DECIMAL(10,2) DEFAULT NULL,
  `tipo_entrada` VARCHAR(30) DEFAULT NULL,
  `id_localizacao` INT UNSIGNED DEFAULT NULL,
  `estado` VARCHAR(30) DEFAULT NULL,
  `criticidade` VARCHAR(30) DEFAULT NULL,
  `observacoes` TEXT DEFAULT NULL,
  `ativo` TINYINT UNSIGNED DEFAULT 1,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_codigo_inventario` (`codigo_inventario`),
  FOREIGN KEY (`id_categoria`) REFERENCES `categorias`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`id_localizacao`) REFERENCES `localizacoes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `equipamentos_fornecedores`;
CREATE TABLE IF NOT EXISTS `equipamentos_fornecedores` (
  `id_equipamento` INT UNSIGNED NOT NULL,
  `id_fornecedor` INT UNSIGNED NOT NULL,
  `tipo_relacao` VARCHAR(50) DEFAULT NULL,
  `observacoes` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_equipamento`, `id_fornecedor`),
  FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `documentos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_equipamento` INT UNSIGNED NOT NULL,
  `id_fornecedor` INT UNSIGNED DEFAULT NULL,
  `tipo_documento` VARCHAR(100) DEFAULT NULL,
  `nome_documento` VARCHAR(200) DEFAULT NULL,
  `data_documento` DATE DEFAULT NULL,
  `data_validade` DATE DEFAULT NULL,
  `nome_ficheiro` VARCHAR(255) DEFAULT NULL,
  `observacoes` TEXT DEFAULT NULL,
  `ativo` TINYINT UNSIGNED DEFAULT 1,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `garantias_contratos`;
CREATE TABLE IF NOT EXISTS `garantias_contratos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_equipamento` INT UNSIGNED NOT NULL,
  `data_inicio` DATE DEFAULT NULL,
  `data_fim` DATE DEFAULT NULL,
  `tem_contrato` TINYINT UNSIGNED DEFAULT 0,
  `tipo_contrato` VARCHAR(100) DEFAULT NULL,
  `entidade_responsavel` VARCHAR(150) DEFAULT NULL,
  `periodicidade` VARCHAR(50) DEFAULT NULL,
  `observacoes` TEXT DEFAULT NULL,
  `ativo` TINYINT UNSIGNED DEFAULT 1,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `mensagens_contacto`;
CREATE TABLE IF NOT EXISTS `mensagens_contacto` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `mensagem` TEXT NOT NULL,
  `lida` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lida` (`lida`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `equipamentos_movimentacoes`;
CREATE TABLE IF NOT EXISTS `equipamentos_movimentacoes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_equipamento` INT UNSIGNED NOT NULL,
  `campo` VARCHAR(50) NOT NULL,
  `valor_anterior` VARCHAR(255) DEFAULT NULL,
  `valor_novo` VARCHAR(255) DEFAULT NULL,
  `alterado_por` VARCHAR(190) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_equipamento_created` (`id_equipamento`, `created_at`),
  FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `emprestimos_equipamentos`;
CREATE TABLE IF NOT EXISTS `emprestimos_equipamentos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_equipamento` INT UNSIGNED NOT NULL,
  `id_localizacao_origem` INT UNSIGNED DEFAULT NULL,
  `id_localizacao_destino` INT UNSIGNED NOT NULL,
  `data_saida` DATE NOT NULL,
  `data_prevista_devolucao` DATE DEFAULT NULL,
  `data_devolucao` DATE DEFAULT NULL,
  `estado` VARCHAR(30) NOT NULL DEFAULT 'Ativo',
  `observacoes` TEXT DEFAULT NULL,
  `created_by` VARCHAR(190) DEFAULT NULL,
  `updated_by` VARCHAR(190) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_equipamento_estado` (`id_equipamento`, `estado`),
  KEY `idx_prevista_devolucao` (`data_prevista_devolucao`),
  FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_localizacao_origem`) REFERENCES `localizacoes`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`id_localizacao_destino`) REFERENCES `localizacoes`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `manutencoes_preventivas`;
CREATE TABLE IF NOT EXISTS `manutencoes_preventivas` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_equipamento` INT UNSIGNED NOT NULL,
  `ultima_manutencao` DATE DEFAULT NULL,
  `proxima_manutencao` DATE DEFAULT NULL,
  `periodicidade` VARCHAR(50) DEFAULT NULL,
  `estado` VARCHAR(30) NOT NULL DEFAULT 'Planeada',
  `tecnico_responsavel` VARCHAR(190) DEFAULT NULL,
  `observacoes` TEXT DEFAULT NULL,
  `created_by` VARCHAR(190) DEFAULT NULL,
  `updated_by` VARCHAR(190) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_equipamento` (`id_equipamento`),
  KEY `idx_proxima_manutencao` (`proxima_manutencao`),
  FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;


TRUNCATE `agents`;
INSERT INTO `agents` (`name`, `passwrd`, `profile`, `created_at`) VALUES
(AES_ENCRYPT('admin@hospital.pt', 'M3dSol_MySQL_AES_2026!'), '$2y$10$M3dSolutions2026HashedPassword123', 'admin', NOW()),
(AES_ENCRYPT('tecnico@hospital.pt', 'M3dSol_MySQL_AES_2026!'), '$2y$10$TecnicoHashedPassword123456789012', 'tecnico', NOW());

INSERT INTO `categorias` (`nome`, `descricao`, `created_at`) VALUES
('Monitorização', 'Equipamentos para monitorizar sinais fisiológicos', NOW()),
('Suporte de vida', 'Equipamentos essenciais para manter funções vitais', NOW()),
('Terapia', 'Equipamentos utilizados para tratamento', NOW()),
('Diagnóstico', 'Equipamentos para avaliação clínica ou imagiologia', NOW()),
('Laboratório', 'Equipamentos usados em análises clínicas', NOW()),
('Esterilização', 'Equipamentos de processamento de dispositivos médicos', NOW()),
('Reabilitação', 'Equipamentos de fisioterapia e recuperação funcional', NOW());

INSERT INTO `localizacoes` (`edificio`, `piso`, `servico`, `sala`, `created_at`) VALUES
('Edifício Principal', 'Piso 2', 'Unidade de Cuidados Intensivos', 'Sala 2.1', NOW()),
('Edifício Principal', 'Piso 2', 'Unidade de Cuidados Intensivos', 'Sala 2.2', NOW()),
('Edifício Principal', 'Piso 0', 'Urgência', 'Sala de Emergência', NOW()),
('Edifício B', 'Piso 1', 'Medicina Interna', 'Enfermaria 1', NOW()),
('Edifício B', 'Piso 0', 'Fisioterapia', 'Sala de Tratamentos', NOW()),
('Edifício C', 'Piso -1', 'Laboratório de Análises', 'Lab. Hematologia', NOW());

INSERT INTO `fornecedores` (`nome`, `nif`, `telefone`, `email`, `morada`, `website`, `pessoa_contacto`, `tel_contacto`, `tipo_fornecedor`, `created_at`) VALUES
('Philips Healthcare', '501234567', '210000001', 'info@philips.pt', 'Rua da Saúde 100, Lisboa', 'https://www.philips.pt', 'João Silva', '912345001', 'Fabricante', NOW()),
('Dräger Portugal', '502345678', '210000002', 'info@draeger.pt', 'Av. da Liberdade 50, Lisboa', 'https://www.draeger.com', 'Ana Costa', '912345002', 'Fabricante', NOW()),
('B. Braun Medical', '503456789', '210000003', 'info@bbraun.pt', 'Rua Industrial 25, Porto', 'https://www.bbraun.pt', 'Carlos Mendes', '912345003', 'Fabricante', NOW()),
('Zoll Medical', '504567890', '210000004', 'info@zoll.pt', 'Rua Nova 10, Lisboa', 'https://www.zoll.com', 'Maria Santos', '912345004', 'Distribuidor', NOW()),
('TecnoMed Assistência', '505678901', '210000005', 'geral@tecnomed.pt', 'Rua Técnica 5, Porto', NULL, 'Pedro Alves', '912345005', 'Assistência técnica', NOW());

