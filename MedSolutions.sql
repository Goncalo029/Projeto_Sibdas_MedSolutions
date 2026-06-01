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
CREATE DATABASE `MedSolutions`;
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


-- ============================================================
-- Dados adicionais para o backoffice
-- ============================================================

USE `MedSolutions`;

INSERT INTO `agents` (`name`, `passwrd`, `profile`, `created_at`)
SELECT AES_ENCRYPT('gestor@hospital.pt', 'M3dSol_MySQL_AES_2026!'), 'gestor123', 'gestor', NOW()
WHERE NOT EXISTS (
  SELECT 1 FROM `agents` WHERE AES_DECRYPT(`name`, 'M3dSol_MySQL_AES_2026!') = 'gestor@hospital.pt'
);

INSERT INTO `categorias` (`nome`, `descricao`, `created_at`)
SELECT 'Cirurgia', 'Equipamentos de apoio a blocos operatorios', NOW()
WHERE NOT EXISTS (SELECT 1 FROM `categorias` WHERE `nome` = 'Cirurgia');

INSERT INTO `categorias` (`nome`, `descricao`, `created_at`)
SELECT 'Imagiologia', 'Equipamentos de diagnostico por imagem', NOW()
WHERE NOT EXISTS (SELECT 1 FROM `categorias` WHERE `nome` = 'Imagiologia');

INSERT INTO `localizacoes` (`edificio`, `piso`, `servico`, `sala`, `created_at`)
SELECT 'Edificio Principal', 'Piso 1', 'Bloco Operatorio', 'Sala 1.3', NOW()
WHERE NOT EXISTS (SELECT 1 FROM `localizacoes` WHERE `servico` = 'Bloco Operatorio' AND `sala` = 'Sala 1.3');

INSERT INTO `localizacoes` (`edificio`, `piso`, `servico`, `sala`, `created_at`)
SELECT 'Edificio C', 'Piso 0', 'Imagiologia', 'Sala RX', NOW()
WHERE NOT EXISTS (SELECT 1 FROM `localizacoes` WHERE `servico` = 'Imagiologia' AND `sala` = 'Sala RX');

INSERT INTO `fornecedores` (`nome`, `nif`, `telefone`, `email`, `morada`, `website`, `pessoa_contacto`, `tel_contacto`, `tipo_fornecedor`, `created_at`)
SELECT 'Medtronic Portugal', '506789012', '210000006', 'info@medtronic.pt', 'Av. Tecnica 12, Lisboa', 'https://www.medtronic.com', 'Rui Fernandes', '912345006', 'Fabricante', NOW()
WHERE NOT EXISTS (SELECT 1 FROM `fornecedores` WHERE `nif` = '506789012');

INSERT INTO `fornecedores` (`nome`, `nif`, `telefone`, `email`, `morada`, `website`, `pessoa_contacto`, `tel_contacto`, `tipo_fornecedor`, `created_at`)
SELECT 'Siemens Healthineers', '507890123', '210000007', 'info@siemens-healthineers.pt', 'Rua da Inovacao 40, Porto', 'https://www.siemens-healthineers.com', 'Sofia Martins', '912345007', 'Fabricante', NOW()
WHERE NOT EXISTS (SELECT 1 FROM `fornecedores` WHERE `nif` = '507890123');

INSERT IGNORE INTO `equipamentos`
(`codigo_inventario`, `designacao`, `id_categoria`, `marca`, `modelo`, `numero_serie`, `fabricante`, `data_aquisicao`, `ano_fabrico`, `custo_aquisicao`, `tipo_entrada`, `id_localizacao`, `estado`, `criticidade`, `observacoes`, `created_at`)
VALUES
('EQ-001', 'Monitor de Sinais Vitais', (SELECT id FROM categorias WHERE nome LIKE 'Monitoriza%' LIMIT 1), 'Philips', 'IntelliVue MX450', 'PH-MX450-001', 'Philips Healthcare', '2024-01-18', 2023, 6800.00, 'Compra', (SELECT id FROM localizacoes WHERE servico LIKE 'Urg%' LIMIT 1), 'Ativo', 'Alta', 'Monitor multiparametrico com ECG, SpO2 e NIBP.', NOW()),
('EQ-002', 'Ventilador UCI', (SELECT id FROM categorias WHERE nome = 'Suporte de vida' LIMIT 1), 'Drager', 'Evita V600', 'DR-EV600-002', 'Drager', '2023-09-04', 2023, 28500.00, 'Compra', (SELECT id FROM localizacoes WHERE servico LIKE 'Unidade de Cuidados Intensivos' LIMIT 1), 'Ativo', 'Suporte de vida', 'Ventilador para cuidados intensivos.', NOW()),
('EQ-003', 'Desfibrilhador AED', (SELECT id FROM categorias WHERE nome LIKE 'Terapia' LIMIT 1), 'Zoll', 'AED 3', 'ZO-AED3-003', 'Zoll Medical', '2022-11-22', 2022, 1950.00, 'Compra', (SELECT id FROM localizacoes WHERE servico LIKE 'Bloco Operatorio' LIMIT 1), 'Manutencao', 'Alta', 'Requer verificacao de bateria.', NOW()),
('EQ-004', 'Bomba de Infusao', (SELECT id FROM categorias WHERE nome = 'Terapia' LIMIT 1), 'B. Braun', 'Infusomat Space', 'BB-INF-004', 'B. Braun Medical', '2024-03-12', 2024, 2400.00, 'Compra', (SELECT id FROM localizacoes WHERE servico LIKE 'Medicina Interna' LIMIT 1), 'Ativo', 'Media', 'Bomba volumetrica para perfusao continua.', NOW()),
('EQ-005', 'Autoclave de Esterilizacao', (SELECT id FROM categorias WHERE nome LIKE 'Esteriliza%' LIMIT 1), 'Tuttnauer', '5596', 'TU-AUT-005', 'Tuttnauer', '2021-06-10', 2021, 18500.00, 'Compra', (SELECT id FROM localizacoes WHERE servico LIKE 'Laboratorio%' LIMIT 1), 'Ativo', 'Media', 'Ciclo de esterilizacao validado.', NOW()),
('EQ-006', 'Ecografo Portatil', (SELECT id FROM categorias WHERE nome = 'Imagiologia' LIMIT 1), 'Siemens', 'Acuson P500', 'SI-ECO-006', 'Siemens Healthineers', '2023-02-14', 2022, 32500.00, 'Compra', (SELECT id FROM localizacoes WHERE servico = 'Imagiologia' LIMIT 1), 'Ativo', 'Alta', 'Ecografo portatil para urgencia e UCI.', NOW()),
('EQ-007', 'Marquesa de Fisioterapia', (SELECT id FROM categorias WHERE nome LIKE 'Reabilita%' LIMIT 1), 'Gymna', 'One', 'GY-MAR-007', 'Gymna', '2020-05-20', 2020, 1250.00, 'Compra', (SELECT id FROM localizacoes WHERE servico LIKE 'Fisioterapia' LIMIT 1), 'Inativo', 'Baixa', 'A aguardar substituicao.', NOW());

INSERT IGNORE INTO `equipamentos_fornecedores` (`id_equipamento`, `id_fornecedor`, `tipo_relacao`, `created_at`)
SELECT e.id, f.id, 'Fornecedor principal', NOW()
FROM equipamentos e
JOIN fornecedores f ON
  (e.marca = 'Philips' AND f.nome = 'Philips Healthcare') OR
  (e.marca = 'Drager' AND f.nome = 'Drager Portugal') OR
  (e.marca = 'Zoll' AND f.nome = 'Zoll Medical') OR
  (e.marca = 'B. Braun' AND f.nome = 'B. Braun Medical') OR
  (e.marca = 'Siemens' AND f.nome = 'Siemens Healthineers');

INSERT INTO `documentos` (`id_equipamento`, `id_fornecedor`, `tipo_documento`, `nome_documento`, `data_documento`, `data_validade`, `nome_ficheiro`, `created_at`)
SELECT e.id, f.id, 'Manual', CONCAT('Manual tecnico ', e.codigo_inventario), '2024-01-01', NULL, CONCAT(e.codigo_inventario, '_manual.pdf'), NOW()
FROM equipamentos e
LEFT JOIN equipamentos_fornecedores ef ON ef.id_equipamento = e.id
LEFT JOIN fornecedores f ON f.id = ef.id_fornecedor
WHERE NOT EXISTS (SELECT 1 FROM documentos d WHERE d.id_equipamento = e.id AND d.tipo_documento = 'Manual');

INSERT INTO `documentos` (`id_equipamento`, `id_fornecedor`, `tipo_documento`, `nome_documento`, `data_documento`, `data_validade`, `nome_ficheiro`, `created_at`)
SELECT e.id, f.id, 'Certificado', CONCAT('Certificado calibracao ', e.codigo_inventario), '2026-01-15', '2027-01-15', CONCAT(e.codigo_inventario, '_calibracao.pdf'), NOW()
FROM equipamentos e
LEFT JOIN equipamentos_fornecedores ef ON ef.id_equipamento = e.id
LEFT JOIN fornecedores f ON f.id = ef.id_fornecedor
WHERE e.codigo_inventario IN ('EQ-001', 'EQ-004', 'EQ-006')
AND NOT EXISTS (SELECT 1 FROM documentos d WHERE d.id_equipamento = e.id AND d.tipo_documento = 'Certificado');

INSERT INTO `garantias_contratos` (`id_equipamento`, `data_inicio`, `data_fim`, `tem_contrato`, `tipo_contrato`, `entidade_responsavel`, `periodicidade`, `created_at`)
SELECT e.id, e.data_aquisicao, DATE_ADD(e.data_aquisicao, INTERVAL 3 YEAR), 1, 'Garantia e manutencao', COALESCE(f.nome, e.fabricante), 'Anual', NOW()
FROM equipamentos e
LEFT JOIN equipamentos_fornecedores ef ON ef.id_equipamento = e.id
LEFT JOIN fornecedores f ON f.id = ef.id_fornecedor
WHERE NOT EXISTS (SELECT 1 FROM garantias_contratos g WHERE g.id_equipamento = e.id);

INSERT INTO `equipamentos_movimentacoes` (`id_equipamento`, `campo`, `valor_anterior`, `valor_novo`, `alterado_por`, `created_at`)
SELECT e.id, 'estado', 'Rececionado', e.estado, 'admin@hospital.pt', NOW()
FROM equipamentos e
WHERE NOT EXISTS (SELECT 1 FROM equipamentos_movimentacoes m WHERE m.id_equipamento = e.id AND m.campo = 'estado');

INSERT INTO `emprestimos_equipamentos` (`id_equipamento`, `id_localizacao_origem`, `id_localizacao_destino`, `data_saida`, `data_prevista_devolucao`, `estado`, `observacoes`, `created_by`, `created_at`, `updated_at`)
SELECT e.id, e.id_localizacao, l.id, '2026-05-10', '2026-06-10', 'Ativo', 'Emprestimo temporario para reforco do servico.', 'admin@hospital.pt', NOW(), NOW()
FROM equipamentos e
JOIN localizacoes l ON l.servico = 'Urgencia'
WHERE e.codigo_inventario = 'EQ-006'
AND NOT EXISTS (SELECT 1 FROM emprestimos_equipamentos emp WHERE emp.id_equipamento = e.id AND emp.estado = 'Ativo');

INSERT INTO `manutencoes_preventivas` (`id_equipamento`, `ultima_manutencao`, `proxima_manutencao`, `periodicidade`, `estado`, `tecnico_responsavel`, `observacoes`, `created_by`, `created_at`, `updated_at`)
SELECT e.id, '2026-01-15', '2027-01-15', 'Anual', 'Planeada', 'tecnico@hospital.pt', 'Plano preventivo anual.', 'admin@hospital.pt', NOW(), NOW()
FROM equipamentos e
WHERE NOT EXISTS (SELECT 1 FROM manutencoes_preventivas mp WHERE mp.id_equipamento = e.id);

