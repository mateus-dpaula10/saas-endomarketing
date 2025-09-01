-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           8.4.3 - MySQL Community Server - GPL
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

-- Copiando estrutura para tabela saas_endomarketing.plains
CREATE TABLE IF NOT EXISTS `plains` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('avulso','mensal') COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `includes_campaigns` tinyint(1) NOT NULL DEFAULT '0',
  `characteristics` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela saas_endomarketing.plains: ~3 rows (aproximadamente)
INSERT IGNORE INTO `plains` (`id`, `name`, `type`, `price`, `includes_campaigns`, `characteristics`, `created_at`, `updated_at`) VALUES
	(1, 'Diagnóstico de Cultura', 'avulso', 49.90, 0, '["Diagnóstico único", "Relatório PDF"]', '2025-09-01 12:58:46', '2025-09-01 12:58:46'),
	(2, 'Diagnóstico de Comunicação Interna', 'avulso', 99.90, 0, '["Diagnóstico único", "Relatório detalhado"]', '2025-09-01 12:58:46', '2025-09-01 12:58:46'),
	(3, 'Comunicação Interna + Campanhas Mensais', 'mensal', 199.90, 1, '["Diagnóstico inicial", "Campanhas mensais automáticas", "Dashboard de resultados"]', '2025-09-01 12:58:46', '2025-09-01 12:58:46');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
