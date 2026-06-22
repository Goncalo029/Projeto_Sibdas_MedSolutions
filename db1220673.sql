SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `equipamento_at`;
DROP TABLE IF EXISTS `manutencoes`;
DROP TABLE IF EXISTS `emprestimos_equipamentos`;
DROP TABLE IF EXISTS `equipamentos_movimentacoes`;
DROP TABLE IF EXISTS `manutencoes_preventivas`;
DROP TABLE IF EXISTS `garantias_contratos`;
DROP TABLE IF EXISTS `documentos`;
DROP TABLE IF EXISTS `equipamentos_fornecedores`;
DROP TABLE IF EXISTS `equipamentos`;
DROP TABLE IF EXISTS `fornecedores`;
DROP TABLE IF EXISTS `localizacoes`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `mensagens_contacto`;
DROP TABLE IF EXISTS `utilizadores`;
DROP TABLE IF EXISTS `agents`;
DROP TABLE IF EXISTS `historico_alteracoes`;
DROP TABLE IF EXISTS `website_config`;

CREATE TABLE `utilizadores` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARBINARY(200) DEFAULT NULL,
  `senha` VARCHAR(200) DEFAULT NULL,
  `perfil` VARCHAR(20) DEFAULT NULL,
  `ultimo_acesso` DATETIME DEFAULT NULL,
  `criado_em` DATETIME DEFAULT NULL,
  `atualizado_em` DATETIME DEFAULT NULL,
  `eliminado_em` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categorias` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `descricao` TEXT DEFAULT NULL,
  `ativo` TINYINT UNSIGNED DEFAULT 1,
  `criado_em` DATETIME DEFAULT NULL,
  `atualizado_em` DATETIME DEFAULT NULL,
  `eliminado_em` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `localizacoes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `edificio` VARCHAR(100) DEFAULT NULL,
  `piso` VARCHAR(50) DEFAULT NULL,
  `servico` VARCHAR(100) NOT NULL,
  `sala` VARCHAR(100) DEFAULT NULL,
  `observacoes` TEXT DEFAULT NULL,
  `ativo` TINYINT UNSIGNED DEFAULT 1,
  `criado_em` DATETIME DEFAULT NULL,
  `atualizado_em` DATETIME DEFAULT NULL,
  `eliminado_em` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `fornecedores` (
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
  `criado_em` DATETIME DEFAULT NULL,
  `atualizado_em` DATETIME DEFAULT NULL,
  `eliminado_em` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `equipamentos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo_inventario` VARCHAR(50) NOT NULL,
  `id_equipamento_pai` INT UNSIGNED DEFAULT NULL,
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
  `criado_em` DATETIME DEFAULT NULL,
  `atualizado_em` DATETIME DEFAULT NULL,
  `eliminado_em` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_codigo_inventario` (`codigo_inventario`),
  FOREIGN KEY (`id_equipamento_pai`) REFERENCES `equipamentos`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`id_categoria`) REFERENCES `categorias`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`id_localizacao`) REFERENCES `localizacoes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `equipamentos_fornecedores` (
  `id_equipamento` INT UNSIGNED NOT NULL,
  `id_fornecedor` INT UNSIGNED NOT NULL,
  `tipo_relacao` VARCHAR(50) DEFAULT NULL,
  `observacoes` TEXT DEFAULT NULL,
  `criado_em` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_equipamento`, `id_fornecedor`),
  FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `documentos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_equipamento` INT UNSIGNED NOT NULL,
  `id_fornecedor` INT UNSIGNED DEFAULT NULL,
  `tipo_documento` VARCHAR(100) DEFAULT NULL,
  `nome_documento` VARCHAR(200) DEFAULT NULL,
  `data_documento` DATE DEFAULT NULL,
  `data_validade` DATE DEFAULT NULL,
  `nome_ficheiro` VARCHAR(255) DEFAULT NULL,
  `ficheiro_conteudo` LONGBLOB DEFAULT NULL,
  `ficheiro_mime` VARCHAR(120) DEFAULT NULL,
  `observacoes` TEXT DEFAULT NULL,
  `ativo` TINYINT UNSIGNED DEFAULT 1,
  `criado_em` DATETIME DEFAULT NULL,
  `atualizado_em` DATETIME DEFAULT NULL,
  `eliminado_em` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `garantias_contratos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_equipamento` INT UNSIGNED NOT NULL,
  `data_inicio` DATE DEFAULT NULL,
  `data_fim` DATE DEFAULT NULL,
  `tem_contrato` TINYINT UNSIGNED DEFAULT 0,
  `tipo_contrato` VARCHAR(100) DEFAULT NULL,
  `entidade_responsavel` VARCHAR(150) DEFAULT NULL,
  `periodicidade` VARCHAR(50) DEFAULT NULL,
  `observacoes` TEXT DEFAULT NULL,
  `nome_ficheiro` VARCHAR(255) DEFAULT NULL,
  `ficheiro_conteudo` LONGBLOB DEFAULT NULL,
  `ficheiro_mime` VARCHAR(120) DEFAULT NULL,
  `ativo` TINYINT UNSIGNED DEFAULT 1,
  `criado_em` DATETIME DEFAULT NULL,
  `atualizado_em` DATETIME DEFAULT NULL,
  `eliminado_em` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mensagens_contacto` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `mensagem` TEXT NOT NULL,
  `lida` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `criado_em` DATETIME NOT NULL,
  `atualizado_em` DATETIME NOT NULL,
  `eliminado_em` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lida` (`lida`),
  KEY `idx_criado_em` (`criado_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `equipamentos_movimentacoes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_equipamento` INT UNSIGNED NOT NULL,
  `campo` VARCHAR(50) NOT NULL,
  `valor_anterior` VARCHAR(255) DEFAULT NULL,
  `valor_novo` VARCHAR(255) DEFAULT NULL,
  `alterado_por` VARCHAR(190) DEFAULT NULL,
  `criado_em` DATETIME NOT NULL,
  `eliminado_em` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_equipamento_created` (`id_equipamento`, `criado_em`),
  FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `emprestimos_equipamentos` (
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
  `criado_em` DATETIME NOT NULL,
  `atualizado_em` DATETIME NOT NULL,
  `eliminado_em` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_equipamento_estado` (`id_equipamento`, `estado`),
  KEY `idx_prevista_devolucao` (`data_prevista_devolucao`),
  FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_localizacao_origem`) REFERENCES `localizacoes`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`id_localizacao_destino`) REFERENCES `localizacoes`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `manutencoes_preventivas` (
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
  `criado_em` DATETIME NOT NULL,
  `atualizado_em` DATETIME NOT NULL,
  `eliminado_em` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_equipamento` (`id_equipamento`),
  KEY `idx_proxima_manutencao` (`proxima_manutencao`),
  FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `manutencoes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_equipamento` INT UNSIGNED NOT NULL,
  `tipo` ENUM('Preventiva','Urgência') NOT NULL DEFAULT 'Preventiva',
  `data_manutencao` DATE DEFAULT NULL,
  `proxima_manutencao` DATE DEFAULT NULL,
  `periodicidade` VARCHAR(50) DEFAULT NULL,
  `estado` VARCHAR(30) NOT NULL DEFAULT 'Planeada',
  `tecnico_responsavel` VARCHAR(190) DEFAULT NULL,
  `descricao` TEXT DEFAULT NULL,
  `observacoes` TEXT DEFAULT NULL,
  `created_by` VARCHAR(190) DEFAULT NULL,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `eliminado_em` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_equipamento` (`id_equipamento`),
  FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `equipamento_at` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_equipamento` INT UNSIGNED NOT NULL,
  `empresa` VARCHAR(255) DEFAULT NULL,
  `nome_contacto` VARCHAR(255) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `telefone` VARCHAR(50) DEFAULT NULL,
  `telefone_urgencia` VARCHAR(50) DEFAULT NULL,
  `observacoes` TEXT DEFAULT NULL,
  `criado_em` DATETIME DEFAULT NULL,
  `atualizado_em` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_eq_at` (`id_equipamento`),
  FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `historico_alteracoes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entidade` VARCHAR(50) NOT NULL,
  `entidade_id` INT UNSIGNED DEFAULT NULL,
  `entidade_nome` VARCHAR(255) DEFAULT NULL,
  `acao` VARCHAR(20) NOT NULL,
  `detalhe` TEXT DEFAULT NULL,
  `utilizador` VARCHAR(190) DEFAULT NULL,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_created` (`criado_em`),
  KEY `idx_entidade` (`entidade`, `entidade_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `website_config` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `chave` VARCHAR(100) NOT NULL,
  `valor` TEXT DEFAULT NULL,
  `atualizado_em` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;

INSERT INTO `utilizadores` (`nome`, `senha`, `perfil`, `criado_em`) VALUES
(AES_ENCRYPT('admin@hospital.pt', 'M3dSol_MySQL_AES_2026!'), '$2y$10$wQ5Z41jT3naiHUy2eNI0NuQLS5mm00.wFNnTJP9g0jZ9GsAnxbIvO', 'admin', NOW()),
(AES_ENCRYPT('tecnico@hospital.pt', 'M3dSol_MySQL_AES_2026!'), '$2y$10$X.oDPE4lxS5WShLYmsuQKOsSRqTPmoCl2K.8Frfsv.AP5Q.RHByEq', 'tecnico', NOW());

INSERT INTO `categorias` (`nome`, `descricao`, `criado_em`) VALUES
('Monitorização', 'Equipamentos para monitorizar sinais fisiológicos', NOW()),
('Suporte de vida', 'Equipamentos essenciais para manter funções vitais', NOW()),
('Terapia', 'Equipamentos utilizados para tratamento', NOW()),
('Diagnóstico', 'Equipamentos para avaliação clínica ou imagiologia', NOW()),
('Laboratório', 'Equipamentos usados em análises clínicas', NOW()),
('Esterilização', 'Equipamentos de processamento de dispositivos médicos', NOW()),
('Reabilitação', 'Equipamentos de fisioterapia e recuperação funcional', NOW()),
('Cirurgia', 'Equipamentos de apoio a blocos operatórios', NOW()),
('Imagiologia', 'Equipamentos de diagnóstico por imagem', NOW());

INSERT INTO `localizacoes` (`edificio`, `piso`, `servico`, `sala`, `criado_em`) VALUES
('Edifício Principal', 'Piso 2', 'Unidade de Cuidados Intensivos', 'Sala 2.1', NOW()),
('Edifício Principal', 'Piso 2', 'Unidade de Cuidados Intensivos', 'Sala 2.2', NOW()),
('Edifício Principal', 'Piso 0', 'Urgência', 'Sala de Emergência', NOW()),
('Edifício B', 'Piso 1', 'Medicina Interna', 'Enfermaria 1', NOW()),
('Edifício B', 'Piso 0', 'Fisioterapia', 'Sala de Tratamentos', NOW()),
('Edifício C', 'Piso -1', 'Laboratório de Análises', 'Lab. Hematologia', NOW()),
('Edifício Principal', 'Piso 1', 'Bloco Operatório', 'Sala 1.3', NOW()),
('Edifício C', 'Piso 0', 'Imagiologia', 'Sala RX', NOW());

INSERT INTO `fornecedores` (`nome`, `nif`, `telefone`, `email`, `morada`, `website`, `pessoa_contacto`, `tel_contacto`, `tipo_fornecedor`, `criado_em`) VALUES
('Philips Healthcare', '501234567', '210000001', 'info@philips.pt', 'Rua da Saúde 100, Lisboa', 'https://www.philips.pt', 'João Silva', '912345001', 'Fabricante', NOW()),
('Dräger Portugal', '502345678', '210000002', 'info@draeger.pt', 'Av. da Liberdade 50, Lisboa', 'https://www.draeger.com', 'Ana Costa', '912345002', 'Fabricante', NOW()),
('B. Braun Medical', '503456789', '210000003', 'info@bbraun.pt', 'Rua Industrial 25, Porto', 'https://www.bbraun.pt', 'Carlos Mendes', '912345003', 'Fabricante', NOW()),
('Zoll Medical', '504567890', '210000004', 'info@zoll.pt', 'Rua Nova 10, Lisboa', 'https://www.zoll.com', 'Maria Santos', '912345004', 'Distribuidor', NOW()),
('TecnoMed Assistência', '505678901', '210000005', 'geral@tecnomed.pt', 'Rua Técnica 5, Porto', NULL, 'Pedro Alves', '912345005', 'Assistência técnica', NOW()),
('Medtronic Portugal', '506789012', '210000006', 'info@medtronic.pt', 'Av. Técnica 12, Lisboa', 'https://www.medtronic.com', 'Rui Fernandes', '912345006', 'Fabricante', NOW()),
('Siemens Healthineers', '507890123', '210000007', 'info@siemens-healthineers.pt', 'Rua da Inovação 40, Porto', 'https://www.siemens-healthineers.com', 'Sofia Martins', '912345007', 'Fabricante', NOW());

INSERT INTO `equipamentos` (`codigo_inventario`, `designacao`, `id_categoria`, `marca`, `modelo`, `numero_serie`, `fabricante`, `data_aquisicao`, `ano_fabrico`, `custo_aquisicao`, `tipo_entrada`, `id_localizacao`, `estado`, `criticidade`, `observacoes`, `criado_em`) VALUES
('EQ-001', 'Monitor de Sinais Vitais', (SELECT id FROM categorias WHERE nome LIKE 'Monitoriza%' LIMIT 1), 'Philips', 'IntelliVue MX450', 'PH-MX450-001', 'Philips Healthcare', '2024-01-18', 2023, 6800.00, 'Compra', (SELECT id FROM localizacoes WHERE servico LIKE 'Urg%' LIMIT 1), 'Ativo', 'Alta', 'Monitor multiparamétrico com ECG, SpO2 e NIBP.', NOW()),
('EQ-002', 'Ventilador UCI', (SELECT id FROM categorias WHERE nome = 'Suporte de vida' LIMIT 1), 'Dräger', 'Evita V600', 'DR-EV600-002', 'Dräger', '2023-09-04', 2023, 28500.00, 'Compra', (SELECT id FROM localizacoes WHERE servico LIKE 'Unidade de Cuidados Intensivos' LIMIT 1), 'Ativo', 'Suporte de vida', 'Ventilador para cuidados intensivos.', NOW()),
('EQ-003', 'Desfibrilhador AED', (SELECT id FROM categorias WHERE nome = 'Terapia' LIMIT 1), 'Zoll', 'AED 3', 'ZO-AED3-003', 'Zoll Medical', '2022-11-22', 2022, 1950.00, 'Compra', (SELECT id FROM localizacoes WHERE servico LIKE 'Bloco Operat%' LIMIT 1), 'Em manutenção', 'Alta', 'Requer verificação de bateria.', NOW()),
('EQ-004', 'Bomba de Infusão', (SELECT id FROM categorias WHERE nome = 'Terapia' LIMIT 1), 'B. Braun', 'Infusomat Space', 'BB-INF-004', 'B. Braun Medical', '2024-03-12', 2024, 2400.00, 'Compra', (SELECT id FROM localizacoes WHERE servico LIKE 'Medicina Interna' LIMIT 1), 'Ativo', 'Média', 'Bomba volumétrica para perfusão contínua.', NOW()),
('EQ-005', 'Autoclave de Esterilização', (SELECT id FROM categorias WHERE nome LIKE 'Esteriliza%' LIMIT 1), 'Tuttnauer', '5596', 'TU-AUT-005', 'Tuttnauer', '2021-06-10', 2021, 18500.00, 'Compra', (SELECT id FROM localizacoes WHERE servico LIKE 'Laborat%' LIMIT 1), 'Ativo', 'Média', 'Ciclo de esterilização validado.', NOW()),
('EQ-006', 'Ecógrafo Portátil', (SELECT id FROM categorias WHERE nome = 'Imagiologia' LIMIT 1), 'Siemens', 'Acuson P500', 'SI-ECO-006', 'Siemens Healthineers', '2023-02-14', 2022, 32500.00, 'Compra', (SELECT id FROM localizacoes WHERE servico = 'Imagiologia' LIMIT 1), 'Ativo', 'Alta', 'Ecógrafo portátil para urgência e UCI.', NOW()),
('EQ-007', 'Marquesa de Fisioterapia', (SELECT id FROM categorias WHERE nome LIKE 'Reabilita%' LIMIT 1), 'Gymna', 'One', 'GY-MAR-007', 'Gymna', '2020-05-20', 2020, 1250.00, 'Compra', (SELECT id FROM localizacoes WHERE servico LIKE 'Fisioterapia' LIMIT 1), 'Inativo', 'Baixa', 'A aguardar substituição.', NOW());

INSERT INTO `equipamentos_fornecedores` (`id_equipamento`, `id_fornecedor`, `tipo_relacao`, `criado_em`)
SELECT e.id, f.id, 'Fornecedor principal', NOW()
FROM equipamentos e
JOIN fornecedores f ON
  (e.marca = 'Philips' AND f.nome = 'Philips Healthcare') OR
  (e.marca = 'Dräger' AND f.nome = 'Dräger Portugal') OR
  (e.marca = 'Zoll' AND f.nome = 'Zoll Medical') OR
  (e.marca = 'B. Braun' AND f.nome = 'B. Braun Medical') OR
  (e.marca = 'Siemens' AND f.nome = 'Siemens Healthineers');

INSERT INTO `documentos` (`id_equipamento`, `id_fornecedor`, `tipo_documento`, `nome_documento`, `data_documento`, `nome_ficheiro`, `criado_em`)
SELECT e.id, f.id, 'Manual de utilizador', CONCAT('Manual técnico ', e.codigo_inventario), '2024-01-01', CONCAT(e.codigo_inventario, '_manual.pdf'), NOW()
FROM equipamentos e
LEFT JOIN equipamentos_fornecedores ef ON ef.id_equipamento = e.id
LEFT JOIN fornecedores f ON f.id = ef.id_fornecedor;

INSERT INTO `documentos` (`id_equipamento`, `id_fornecedor`, `tipo_documento`, `nome_documento`, `data_documento`, `data_validade`, `nome_ficheiro`, `criado_em`)
SELECT e.id, f.id, 'Certificado de calibração', CONCAT('Certificado calibração ', e.codigo_inventario), '2026-01-15', '2027-01-15', CONCAT(e.codigo_inventario, '_calibracao.pdf'), NOW()
FROM equipamentos e
LEFT JOIN equipamentos_fornecedores ef ON ef.id_equipamento = e.id
LEFT JOIN fornecedores f ON f.id = ef.id_fornecedor
WHERE e.codigo_inventario IN ('EQ-001', 'EQ-004', 'EQ-006');

INSERT INTO `garantias_contratos` (`id_equipamento`, `data_inicio`, `data_fim`, `tem_contrato`, `tipo_contrato`, `entidade_responsavel`, `periodicidade`, `criado_em`)
SELECT e.id, e.data_aquisicao, DATE_ADD(e.data_aquisicao, INTERVAL 3 YEAR), 1, 'Garantia e manutenção', COALESCE(f.nome, e.fabricante), 'Anual', NOW()
FROM equipamentos e
LEFT JOIN equipamentos_fornecedores ef ON ef.id_equipamento = e.id
LEFT JOIN fornecedores f ON f.id = ef.id_fornecedor;

INSERT INTO `equipamentos_movimentacoes` (`id_equipamento`, `campo`, `valor_anterior`, `valor_novo`, `alterado_por`, `criado_em`)
SELECT e.id, 'estado', 'Rececionado', e.estado, 'admin@hospital.pt', NOW()
FROM equipamentos e;

INSERT INTO `equipamentos_movimentacoes` (`id_equipamento`, `campo`, `valor_anterior`, `valor_novo`, `alterado_por`, `criado_em`)
SELECT e.id, 'localizacao', 'Armazém Central', CONCAT(l.servico, ' - ', l.sala), 'admin@hospital.pt', DATE_SUB(NOW(), INTERVAL 90 DAY)
FROM equipamentos e
JOIN localizacoes l ON l.id = e.id_localizacao;

INSERT INTO `equipamentos_movimentacoes` (`id_equipamento`, `campo`, `valor_anterior`, `valor_novo`, `alterado_por`, `criado_em`)
SELECT e.id, 'localizacao', 'Bloco Operatório - Sala 2', 'Urgência - Sala de Reanimação', 'tecnico@hospital.pt', DATE_SUB(NOW(), INTERVAL 30 DAY)
FROM equipamentos e
WHERE e.codigo_inventario = 'EQ-001';

INSERT INTO `equipamentos_movimentacoes` (`id_equipamento`, `campo`, `valor_anterior`, `valor_novo`, `alterado_por`, `criado_em`)
SELECT e.id, 'estado', 'Ativo', 'Em manutenção', 'tecnico@hospital.pt', DATE_SUB(NOW(), INTERVAL 7 DAY)
FROM equipamentos e
WHERE e.codigo_inventario = 'EQ-003';

INSERT INTO `emprestimos_equipamentos` (`id_equipamento`, `id_localizacao_origem`, `id_localizacao_destino`, `data_saida`, `data_prevista_devolucao`, `data_devolucao`, `estado`, `observacoes`, `created_by`, `criado_em`, `atualizado_em`)
SELECT e.id, e.id_localizacao,
  (SELECT id FROM localizacoes WHERE servico LIKE 'Bloco Operat%' LIMIT 1),
  DATE_SUB(CURDATE(), INTERVAL 14 DAY), DATE_SUB(CURDATE(), INTERVAL 14 DAY), DATE_SUB(CURDATE(), INTERVAL 14 DAY),
  'Devolvido', 'Empréstimo de algumas horas para cirurgia programada. Devolvido no próprio dia.', 'admin@hospital.pt', NOW(), NOW()
FROM equipamentos e
WHERE e.codigo_inventario = 'EQ-006';

INSERT INTO `emprestimos_equipamentos` (`id_equipamento`, `id_localizacao_origem`, `id_localizacao_destino`, `data_saida`, `data_prevista_devolucao`, `data_devolucao`, `estado`, `observacoes`, `created_by`, `criado_em`, `atualizado_em`)
SELECT e.id, e.id_localizacao,
  (SELECT id FROM localizacoes WHERE servico LIKE 'Urg%' LIMIT 1),
  DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_SUB(CURDATE(), INTERVAL 4 DAY), DATE_SUB(CURDATE(), INTERVAL 4 DAY),
  'Devolvido', 'Empréstimo de um dia para reforço na urgência.', 'tecnico@hospital.pt', NOW(), NOW()
FROM equipamentos e
WHERE e.codigo_inventario = 'EQ-004';

INSERT INTO `emprestimos_equipamentos` (`id_equipamento`, `id_localizacao_origem`, `id_localizacao_destino`, `data_saida`, `data_prevista_devolucao`, `data_devolucao`, `estado`, `observacoes`, `created_by`, `criado_em`, `atualizado_em`)
SELECT e.id, e.id_localizacao,
  (SELECT id FROM localizacoes WHERE servico LIKE 'Unidade de Cuidados Intensivos' LIMIT 1),
  CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 DAY), NULL,
  'Ativo', 'Empréstimo temporário à UCI. Devolução prevista para amanhã.', 'admin@hospital.pt', NOW(), NOW()
FROM equipamentos e
WHERE e.codigo_inventario = 'EQ-001';

INSERT INTO `manutencoes_preventivas` (`id_equipamento`, `ultima_manutencao`, `proxima_manutencao`, `periodicidade`, `estado`, `tecnico_responsavel`, `observacoes`, `created_by`, `criado_em`, `atualizado_em`)
SELECT e.id,
  CASE WHEN e.criticidade IN ('Alta', 'Suporte de vida') THEN DATE_SUB(CURDATE(), INTERVAL 3 MONTH) ELSE DATE_SUB(CURDATE(), INTERVAL 6 MONTH) END,
  CASE WHEN e.criticidade IN ('Alta', 'Suporte de vida') THEN DATE_ADD(CURDATE(), INTERVAL 3 MONTH) ELSE DATE_ADD(CURDATE(), INTERVAL 6 MONTH) END,
  CASE WHEN e.criticidade IN ('Alta', 'Suporte de vida') THEN 'Semestral' ELSE 'Anual' END,
  'Planeada', 'tecnico@hospital.pt',
  CASE WHEN e.criticidade IN ('Alta', 'Suporte de vida') THEN 'Manutenção preventiva semestral (2x por ano) — equipamento crítico.' ELSE 'Manutenção preventiva anual.' END,
  'admin@hospital.pt', NOW(), NOW()
FROM equipamentos e;

INSERT INTO `manutencoes` (`id_equipamento`, `tipo`, `data_manutencao`, `proxima_manutencao`, `periodicidade`, `estado`, `tecnico_responsavel`, `descricao`, `created_by`, `criado_em`)
SELECT e.id, 'Preventiva', DATE_SUB(CURDATE(), INTERVAL 9 MONTH), DATE_SUB(CURDATE(), INTERVAL 3 MONTH), 'Semestral', 'Concluída', 'tecnico@hospital.pt',
  'Manutenção preventiva semestral: verificação geral, limpeza e testes funcionais.', 'admin@hospital.pt', DATE_SUB(NOW(), INTERVAL 9 MONTH)
FROM equipamentos e
WHERE e.criticidade IN ('Alta','Suporte de vida');

INSERT INTO `manutencoes` (`id_equipamento`, `tipo`, `data_manutencao`, `proxima_manutencao`, `periodicidade`, `estado`, `tecnico_responsavel`, `descricao`, `created_by`, `criado_em`)
SELECT e.id, 'Preventiva', DATE_SUB(CURDATE(), INTERVAL 3 MONTH), DATE_ADD(CURDATE(), INTERVAL 3 MONTH), 'Semestral', 'Concluída', 'tecnico@hospital.pt',
  'Manutenção preventiva semestral: calibração, verificação de alarmes e segurança elétrica.', 'admin@hospital.pt', DATE_SUB(NOW(), INTERVAL 3 MONTH)
FROM equipamentos e
WHERE e.criticidade IN ('Alta','Suporte de vida');

INSERT INTO `manutencoes` (`id_equipamento`, `tipo`, `data_manutencao`, `proxima_manutencao`, `periodicidade`, `estado`, `tecnico_responsavel`, `descricao`, `created_by`, `criado_em`)
SELECT e.id, 'Preventiva', DATE_SUB(CURDATE(), INTERVAL 18 MONTH), DATE_SUB(CURDATE(), INTERVAL 6 MONTH), 'Anual', 'Concluída', 'tecnico@hospital.pt',
  'Manutenção preventiva anual: inspeção geral e testes de funcionamento.', 'admin@hospital.pt', DATE_SUB(NOW(), INTERVAL 18 MONTH)
FROM equipamentos e
WHERE e.criticidade NOT IN ('Alta','Suporte de vida');

INSERT INTO `manutencoes` (`id_equipamento`, `tipo`, `data_manutencao`, `proxima_manutencao`, `periodicidade`, `estado`, `tecnico_responsavel`, `descricao`, `created_by`, `criado_em`)
SELECT e.id, 'Preventiva', DATE_SUB(CURDATE(), INTERVAL 6 MONTH), DATE_ADD(CURDATE(), INTERVAL 6 MONTH), 'Anual', 'Concluída', 'tecnico@hospital.pt',
  'Manutenção preventiva anual: inspeção geral, limpeza e verificação de segurança.', 'admin@hospital.pt', DATE_SUB(NOW(), INTERVAL 6 MONTH)
FROM equipamentos e
WHERE e.criticidade NOT IN ('Alta','Suporte de vida');

INSERT INTO `manutencoes` (`id_equipamento`, `tipo`, `data_manutencao`, `proxima_manutencao`, `periodicidade`, `estado`, `tecnico_responsavel`, `descricao`, `created_by`, `criado_em`)
SELECT e.id, 'Urgência', DATE_SUB(CURDATE(), INTERVAL 7 DAY), NULL, NULL, 'Em curso', 'tecnico@hospital.pt',
  'Intervenção urgente: falha na verificação de bateria. Substituição em curso.', 'tecnico@hospital.pt', DATE_SUB(NOW(), INTERVAL 7 DAY)
FROM equipamentos e
WHERE e.codigo_inventario = 'EQ-003';

INSERT INTO `equipamento_at` (`id_equipamento`, `empresa`, `nome_contacto`, `email`, `telefone`, `telefone_urgencia`, `observacoes`, `criado_em`, `atualizado_em`)
SELECT id, 'Philips Healthcare — Assistência', 'João Marques', 'suporte.pt@philips.com', '210 000 911', '800 100 100',
       'Contrato de assistência dedicado ao monitor de sinais vitais.', NOW(), NOW()
FROM equipamentos WHERE codigo_inventario = 'EQ-001';

INSERT INTO `equipamento_at` (`id_equipamento`, `empresa`, `nome_contacto`, `email`, `telefone`, `telefone_urgencia`, `observacoes`, `criado_em`, `atualizado_em`)
SELECT id, 'Dräger Portugal — Serviço Técnico', 'Ana Sousa', 'servico@draeger.pt', '210 000 922', '808 200 200',
       'Assistência especializada em ventilação (UCI). Resposta em 24h.', NOW(), NOW()
FROM equipamentos WHERE codigo_inventario = 'EQ-002';

INSERT INTO `equipamento_at` (`id_equipamento`, `empresa`, `nome_contacto`, `email`, `telefone`, `telefone_urgencia`, `observacoes`, `criado_em`, `atualizado_em`)
SELECT id, 'Siemens Healthineers — AT Imagiologia', 'Carlos Pinto', 'at.imagiologia@siemens-healthineers.pt', '210 000 933', NULL,
       'Calibração e manutenção do ecógrafo asseguradas pelo fabricante.', NOW(), NOW()
FROM equipamentos WHERE codigo_inventario = 'EQ-006';


-- Conteudo PDF de exemplo para os documentos do seed (guardado na BD, nao em pasta)
UPDATE `documentos` SET `ficheiro_conteudo` = 0x255044462d312e340a312030206f626a0a3c3c202f54797065202f436174616c6f67202f5061676573203220302052203e3e0a656e646f626a0a322030206f626a0a3c3c202f54797065202f5061676573202f4b696473205b33203020525d202f436f756e742031203e3e0a656e646f626a0a332030206f626a0a3c3c202f54797065202f50616765202f506172656e74203220302052202f4d65646961426f78205b30203020353935203834325d202f5265736f7572636573203c3c202f466f6e74203c3c202f4631203520302052202f4632203620302052203e3e203e3e202f436f6e74656e7473203420302052203e3e0a656e646f626a0a342030206f626a0a3c3c202f4c656e67746820343534203e3e0a73747265616d0a302e31313420302e31363120302e333032207267203430203738302035313520343020726520662031203120312072670a4254202f46322032302054662035342037393220546420284d616e75616c205465636e69636f202d2045512d3030312920546a2045540a30203020302072670a4254202f46312031322054662035342037333020546420284d6f6e69746f722064652053696e61697320566974616973205c285068696c69707320496e74656c6c69567565204d583435305c292920546a2045540a4254202f46312031312054662035342036393520546420284d6564536f6c7574696f6e73202d2047657374616f206465204571756970616d656e746f73204d656469636f732920546a2045540a4254202f4631203130205466203534203637352054642028446f63756d656e746f20646520616d6f737472612067657261646f20706172612064656d6f6e7374726163616f2e2920546a2045540a4254202f46312031302054662035342036353520546420284461746120646520656d697373616f3a2031342f30362f323032362920546a2045540a302e38207720302e3720302e3720302e3720524720353420363435206d2035343120363435206c20530a656e6473747265616d0a656e646f626a0a352030206f626a0a3c3c202f54797065202f466f6e74202f53756274797065202f5479706531202f42617365466f6e74202f48656c766574696361202f456e636f64696e67202f57696e416e7369456e636f64696e67203e3e0a656e646f626a0a362030206f626a0a3c3c202f54797065202f466f6e74202f53756274797065202f5479706531202f42617365466f6e74202f48656c7665746963612d426f6c64202f456e636f64696e67202f57696e416e7369456e636f64696e67203e3e0a656e646f626a0a787265660a3020370a303030303030303030302036353533352066200a30303030303030303039203030303030206e200a30303030303030303538203030303030206e200a30303030303030313135203030303030206e200a30303030303030323531203030303030206e200a30303030303030373535203030303030206e200a30303030303030383532203030303030206e200a747261696c65720a3c3c202f53697a652037202f526f6f74203120302052203e3e0a7374617274787265660a3935340a2525454f46, `ficheiro_mime` = 'application/pdf' WHERE `nome_ficheiro` IS NOT NULL AND `ficheiro_conteudo` IS NULL;

INSERT INTO `website_config` (`chave`, `valor`, `atualizado_em`) VALUES
('titulo', 'MedSolutions', NOW()),
('subtitulo', 'Sistema de Gestão de Inventário Hospitalar', NOW()),
('descricao', 'Plataforma integrada para gestão de equipamentos médicos, manutenções e inventário hospitalar.', NOW()),
('email_contacto', 'geral@medsolutions.pt', NOW()),
('telefone', '+351 210 000 000', NOW()),
('morada', 'Rua da Saúde, 100 — 4000-001 Porto, Portugal', NOW()),
('horario', 'Segunda a Sexta: 08h00 — 18h00', NOW());
