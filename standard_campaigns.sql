-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           10.4.32-MariaDB - mariadb.org binary distribution
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Copiando estrutura para tabela saas-endomarketing.standard_campaigns
CREATE TABLE IF NOT EXISTS `standard_campaigns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_code` varchar(255) NOT NULL,
  `trigger_max_score` float NOT NULL,
  `text` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela saas-endomarketing.standard_campaigns: ~8 rows (aproximadamente)
INSERT IGNORE INTO `standard_campaigns` (`id`, `category_code`, `trigger_max_score`, `text`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'comu_inte', 2.5, 'Feedback Construtivo', 'Campanha de incentivo à escuta ativa, clareza de mensagens e fortalecimento dos canais de comunicação interna.', 1, '2025-07-14 17:20:51', '2025-07-14 17:20:51'),
	(2, 'reco_valo', 2.5, 'Reconhecer e Valorizar', 'Campanha de valorização e reconhecimento do trabalho dos colaboradores, com práticas de feedback positivo e celebração de conquistas.', 1, '2025-07-14 17:20:51', '2025-07-14 17:20:51'),
	(3, 'clim_orga', 2.5, 'Time em Foco', 'Campanha para fortalecer o ambiente organizacional, com ações de integração, escuta ativa e celebração do time.', 1, '2025-07-14 17:20:51', '2025-07-14 17:20:51'),
	(4, 'cult_orga', 2.5, 'Cultura que Inspira', 'Campanha para reforçar os valores da empresa, promover a identidade organizacional e alinhar comportamentos esperados.', 1, '2025-07-14 17:20:51', '2025-07-14 17:20:51'),
	(5, 'dese_capa', 2.5, 'Trilhas de Crescimento', 'Campanha de incentivo à capacitação contínua, com treinamentos, mentorias e desenvolvimento de habilidades técnicas e comportamentais.', 1, '2025-07-14 17:20:51', '2025-07-14 17:20:51'),
	(6, 'lide_gest', 2.5, 'Liderança Positiva', 'Campanha para desenvolver competências de liderança, com foco em escuta ativa, apoio ao time e clareza de direcionamento.', 1, '2025-07-14 17:20:51', '2025-07-14 17:20:51'),
	(7, 'qual_vida_trab', 2.5, 'Saúde em Dia', 'Campanha voltada ao bem-estar físico, emocional e mental dos colaboradores, com ações de saúde, equilíbrio e apoio.', 1, '2025-07-14 17:20:51', '2025-07-14 17:20:51'),
	(8, 'pert_enga', 2.5, 'Juntos Somos Mais', 'Campanha de fortalecimento do senso de pertencimento, identidade e engajamento dos colaboradores com a cultura da empresa.', 1, '2025-07-14 17:20:51', '2025-07-14 17:20:51');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
